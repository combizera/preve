<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\TransactionType;
use App\Models\CreditCard;
use App\Models\Transaction;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection as SupportCollection;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Throwable;

final class CreditCardService
{
    /**
     * Create a card purchase, fanning installments out into one transaction per
     * future invoice. The user-supplied date is the purchase date; each row's
     * transaction_date is the due date of the invoice it lands in. Returns the
     * first (parent) transaction; the remaining installments reference it.
     *
     * @param  array<string, mixed>  $data
     * @param  array<int>  $tagIds
     *
     * @throws Throwable
     */
    public function createPurchase(User $user, CreditCard $card, array $data, int $splits, array $tagIds): Transaction
    {
        $purchaseDate = Date::parse($data['transaction_date']);
        $firstDueDate = $card->effectivePaymentDate($purchaseDate);
        $amounts = $this->splitAmount((int) $data['amount'], $splits);

        $base = Arr::except($data, ['credit_card_id', 'amount', 'transaction_date']);

        return DB::transaction(function () use ($user, $card, $base, $amounts, $splits, $purchaseDate, $firstDueDate, $tagIds): Transaction {
            $parent = null;

            foreach ($amounts as $index => $amount) {
                $transaction = $user->transactions()->create([
                    ...$base,
                    'credit_card_id'        => $card->id,
                    'amount'                => $amount,
                    'purchase_date'         => $purchaseDate,
                    'transaction_date'      => $this->installmentDueDate($firstDueDate, $index, $card->due_day),
                    'split_number'          => $splits > 1 ? $index + 1 : null,
                    'split_total'           => $splits > 1 ? $splits : null,
                    'parent_transaction_id' => $parent?->id,
                ]);

                $parent ??= $transaction;
                $transaction->tags()->sync($tagIds);
            }

            return $parent;
        });
    }

    /**
     * Resolve the purchase/effective dates for a single card transaction (no
     * installment fan-out), used when updating an existing card transaction.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function resolveSingle(CreditCard $card, array $data): array
    {
        $purchaseDate = Date::parse($data['transaction_date']);

        $data['purchase_date'] = $purchaseDate;
        $data['transaction_date'] = $card->effectivePaymentDate($purchaseDate);

        return $data;
    }

    /**
     * Move existing transactions onto a card in bulk. Only the user's own expense
     * transactions without a savings bucket are eligible; each keeps its current
     * date as the purchase date and is rebilled on the invoice due date. Returns
     * how many were moved.
     *
     * @param  array<int, string>  $ids
     *
     * @throws Throwable
     */
    public function assignTransactionsToCard(User $user, CreditCard $card, array $ids): int
    {
        $transactions = $user->transactions()
            ->whereIn('id', $ids)
            ->where('type', TransactionType::EXPENSE)
            ->whereNull('savings_bucket_id')
            ->get();

        return DB::transaction(function () use ($transactions, $card): int {
            $transactions->each(function (Transaction $transaction) use ($card): void {
                $purchaseDate = $transaction->transaction_date;

                $transaction->update([
                    'credit_card_id'   => $card->id,
                    'purchase_date'    => $purchaseDate,
                    'transaction_date' => $card->effectivePaymentDate($purchaseDate),
                ]);
            });

            return $transactions->count();
        });
    }

    /**
     * Everything the credit cards screen needs: each card with its committed and
     * current-invoice totals, the aggregate summary, and the upcoming invoices
     * broken down by card.
     *
     * @return array{
     *     creditCards: Collection<int, CreditCard>,
     *     summary: array{committed: int, limit: int, available: int, invoice: int},
     *     upcomingInvoices: list<array{year: int, month: int, totals: array<int, int>}>
     * }
     */
    public function overview(User $user, CarbonInterface $now): array
    {
        $creditCards = $user->creditCards()->orderBy('name')->get();
        $committedByCard = $this->committedTotals($user, $now);
        $invoiceByCard = $this->invoiceTotals($user, $now);

        $creditCards->each(function (CreditCard $card) use ($committedByCard, $invoiceByCard): void {
            $card->setAttribute('committed', (int) ($committedByCard[$card->id] ?? 0));
            $card->setAttribute('current_invoice', (int) ($invoiceByCard[$card->id] ?? 0));
        });

        $totalCommitted = (int) $committedByCard->sum();
        $totalLimit = (int) $creditCards->whereNotNull('credit_limit')->sum('credit_limit');

        return [
            'creditCards' => $creditCards,
            'summary'     => [
                'committed' => $totalCommitted,
                'limit'     => $totalLimit,
                'available' => max(0, $totalLimit - $totalCommitted),
                'invoice'   => (int) $invoiceByCard->sum(),
            ],
            'upcomingInvoices' => $this->upcomingInvoices($user, $now, $creditCards),
        ];
    }

    /**
     * The user's cards, each with the total of its invoice due in the given month.
     *
     * @return Collection<int, CreditCard>
     */
    public function withMonthlyInvoice(User $user, CarbonInterface $month): Collection
    {
        $invoiceByCard = $this->invoiceTotals($user, $month);

        return $user->creditCards()
            ->orderBy('name')
            ->get()
            ->each(fn (CreditCard $card) => $card->setAttribute('current_invoice', (int) ($invoiceByCard[$card->id] ?? 0)));
    }

    /**
     * Resolve a 'YYYY-MM' string into the first day of that month, falling back
     * to the current month when the input is missing or malformed.
     */
    public function resolveMonth(?string $month): CarbonInterface
    {
        if (is_string($month)) {
            try {
                return Date::createFromFormat('Y-m', $month)->startOfMonth();
            } catch (Throwable) {
                // fall through to the current month
            }
        }

        return Date::now()->startOfMonth();
    }

    /**
     * The transactions composing a card's invoice for the given month (by due
     * date) and the invoice total (charges minus refunds).
     *
     * @return array{transactions: Collection<int, Transaction>, total: int}
     */
    public function invoiceFor(CreditCard $card, CarbonInterface $month): array
    {
        $transactions = $card->transactions()
            ->with('category')
            ->whereYear('transaction_date', $month->year)
            ->whereMonth('transaction_date', $month->month)
            ->oldest('transaction_date')
            ->oldest('purchase_date')
            ->get();

        $total = (int) $transactions->reduce(
            fn (int $carry, Transaction $transaction): int => $carry + ($transaction->type === TransactionType::EXPENSE ? $transaction->amount : -$transaction->amount),
            0,
        );

        return ['transactions' => $transactions, 'total' => $total];
    }

    /**
     * Card spend not yet debited (due today or later), summed per card.
     *
     * @return SupportCollection<int, int>
     */
    private function committedTotals(User $user, CarbonInterface $now): SupportCollection
    {
        return $user->transactions()
            ->whereNotNull('credit_card_id')
            ->expense()
            ->where('transaction_date', '>=', $now->copy()->startOfDay()->toDateString())
            ->selectRaw('credit_card_id, SUM(amount) as total')
            ->groupBy('credit_card_id')
            ->pluck('total', 'credit_card_id');
    }

    /**
     * Card spend due within the given month, summed per card.
     *
     * @return SupportCollection<int, int>
     */
    private function invoiceTotals(User $user, CarbonInterface $month): SupportCollection
    {
        return $user->transactions()
            ->whereNotNull('credit_card_id')
            ->expense()
            ->inMonth($month)
            ->selectRaw('credit_card_id, SUM(amount) as total')
            ->groupBy('credit_card_id')
            ->pluck('total', 'credit_card_id');
    }

    /**
     * Total card spend per upcoming invoice month, broken down by card, for the
     * current month and the following five. Keyed by card id so the chart can
     * stack one series per card.
     *
     * @param  Collection<int, CreditCard>  $creditCards
     * @return list<array{year: int, month: int, totals: array<int, int>}>
     */
    private function upcomingInvoices(User $user, CarbonInterface $now, Collection $creditCards): array
    {
        $start = $now->copy()->startOfMonth();
        $end = $start->copy()->addMonths(5)->endOfMonth();

        $rows = $user->transactions()
            ->whereNotNull('credit_card_id')
            ->expense()
            ->whereBetween('transaction_date', [$start->toDateString(), $end->toDateString()])
            ->selectRaw('credit_card_id, EXTRACT(YEAR FROM transaction_date)::int as y, EXTRACT(MONTH FROM transaction_date)::int as m, SUM(amount) as total')
            ->groupBy('credit_card_id', 'y', 'm')
            ->get();

        return collect(range(0, 5))
            ->map(function (int $offset) use ($start, $rows, $creditCards): array {
                $month = $start->copy()->addMonths($offset);

                $totals = [];
                foreach ($creditCards as $card) {
                    $totals[$card->id] = (int) $rows
                        ->where('credit_card_id', $card->id)
                        ->where('y', $month->year)
                        ->where('m', $month->month)
                        ->sum('total');
                }

                return ['year' => $month->year, 'month' => $month->month, 'totals' => $totals];
            })
            ->all();
    }

    private function installmentDueDate(CarbonInterface $firstDueDate, int $offsetMonths, int $dueDay): CarbonInterface
    {
        $month = $firstDueDate->copy()->addMonthsNoOverflow($offsetMonths);

        return $month->setDay(min($dueDay, $month->daysInMonth))->startOfDay();
    }

    /**
     * Split a total into N installments, giving the remainder cents to the first.
     *
     * @return list<int>
     */
    private function splitAmount(int $total, int $splits): array
    {
        $base = intdiv($total, $splits);
        $remainder = $total - ($base * $splits);

        return array_map(
            fn (int $index): int => $index === 0 ? $base + $remainder : $base,
            range(0, $splits - 1),
        );
    }
}
