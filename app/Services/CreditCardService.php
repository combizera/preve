<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\CreditCard;
use App\Models\Transaction;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Support\Arr;
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
