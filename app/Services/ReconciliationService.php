<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\AccentColor;
use App\Enums\CategoryIcon;
use App\Enums\TransactionType;
use App\Models\Category;
use App\Models\Hiatus;
use App\Models\RecurringTransaction;
use App\Models\SavingsBucket;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Date;

final readonly class ReconciliationService
{
    public function __construct(private InactivityService $inactivity) {}

    /**
     * @return array{hiatus: Hiatus|null, suggestedStart: string|null, pendingRecurring: Collection<int, Transaction>, activeRecurring: Collection<int, RecurringTransaction>, savingsBuckets: Collection<int, SavingsBucket>, currentBalance: int}
     */
    public function overview(User $user): array
    {
        $hiatus = $this->currentHiatus($user);

        return [
            'hiatus'           => $hiatus,
            'suggestedStart'   => $this->inactivity->lastManualActivity($user)?->toDateString(),
            'pendingRecurring' => $hiatus instanceof Hiatus ? $this->pendingRecurringTransactions($user, $hiatus) : collect(),
            'activeRecurring'  => $user
                ->recurringTransactions()
                ->where('is_active', true)
                ->with('category')
                ->orderBy('description')
                ->get(),
            'savingsBuckets' => $user->savingsBuckets()->orderBy('id')->get(),
            'currentBalance' => $this->currentBalance($user),
        ];
    }

    public function currentBalance(User $user): int
    {
        return (int) $user
            ->transactions()
            ->paid(Date::now())
            ->netBalance()
            ->value('net_balance');
    }

    public function currentHiatus(User $user): ?Hiatus
    {
        return $user
            ->hiatuses()
            ->whereNull('reconciled_at')
            ->where('started_at', '<=', Date::today()->toDateString())
            ->latest('started_at')
            ->first();
    }

    /**
     * Recurring-generated transactions whose competence date (purchase_date for
     * card purchases, transaction_date otherwise) falls inside the hiatus — the
     * ones the user needs to confirm actually happened while they were away.
     *
     * @return Collection<int, Transaction>
     */
    public function pendingRecurringTransactions(User $user, Hiatus $hiatus): Collection
    {
        $start = $hiatus->started_at->toDateString();
        $end = ($hiatus->ended_at ?? Date::today())->toDateString();

        return $user
            ->transactions()
            ->whereNotNull('recurring_transaction_id')
            ->where(fn (Builder $query) => $query
                ->where(fn (Builder $cardScope) => $cardScope
                    ->whereNotNull('purchase_date')
                    ->whereBetween('purchase_date', [$start, $end]))
                ->orWhere(fn (Builder $cashScope) => $cashScope
                    ->whereNull('purchase_date')
                    ->whereBetween('transaction_date', [$start, $end])))
            ->with(['category', 'recurringTransaction'])
            ->orderBy('transaction_date')
            ->get();
    }

    /**
     * @param  list<string>  $deleteTransactionIds
     * @param  list<string>  $deactivateRecurringIds
     */
    public function reviewRecurring(User $user, array $deleteTransactionIds, array $deactivateRecurringIds): void
    {
        if ($deleteTransactionIds !== []) {
            $user
                ->transactions()
                ->whereNotNull('recurring_transaction_id')
                ->whereIn('id', $deleteTransactionIds)
                ->delete();
        }

        if ($deactivateRecurringIds === []) {
            return;
        }

        $startOfNextMonth = Date::now()->endOfMonth()->addDay()->startOfDay();

        $user
            ->recurringTransactions()
            ->whereIn('id', $deactivateRecurringIds)
            ->get()
            ->each(function (RecurringTransaction $recurring) use ($startOfNextMonth): void {
                $recurring->update(['is_active' => false]);

                $recurring
                    ->transactions()
                    ->where(fn (Builder $query) => $query
                        ->where(fn (Builder $cardScope) => $cardScope
                            ->whereNotNull('purchase_date')
                            ->where('purchase_date', '>=', $startOfNextMonth))
                        ->orWhere(fn (Builder $cashScope) => $cashScope
                            ->whereNull('purchase_date')
                            ->where('transaction_date', '>=', $startOfNextMonth)))
                    ->delete();
            });
    }

    /**
     * Every correction is a real transaction — never a direct write to
     * denormalized amounts — so savings history and charts stay consistent.
     *
     * @param  array<int, array{id: int, real_amount: int}>  $buckets
     */
    public function adjustBuckets(User $user, array $buckets): int
    {
        $adjusted = 0;

        foreach ($buckets as $row) {
            $bucket = $user->savingsBuckets()->find($row['id']);

            if ($bucket === null) {
                continue;
            }

            $difference = $row['real_amount'] - $bucket->current_amount;

            if ($difference === 0) {
                continue;
            }

            $type = $difference > 0 ? TransactionType::EXPENSE : TransactionType::INCOME;

            $user->transactions()->create([
                'category_id'       => $this->adjustmentCategory($user, $type)->id,
                'savings_bucket_id' => $bucket->id,
                'amount'            => abs($difference),
                'type'              => $type,
                'description'       => __('messages.reconciliation.bucket_adjustment', ['bucket' => $bucket->name], $user->locale),
                'transaction_date'  => Date::today(),
            ]);

            $adjusted++;
        }

        return $adjusted;
    }

    public function adjustBalance(User $user, int $realBalance): ?Transaction
    {
        $difference = $realBalance - $this->currentBalance($user);

        if ($difference === 0) {
            return null;
        }

        $type = $difference > 0 ? TransactionType::INCOME : TransactionType::EXPENSE;

        return $user->transactions()->create([
            'category_id'      => $this->adjustmentCategory($user, $type)->id,
            'amount'           => abs($difference),
            'type'             => $type,
            'description'      => __('messages.reconciliation.balance_adjustment', [], $user->locale),
            'transaction_date' => Date::today(),
        ]);
    }

    /**
     * Closing also caps an open-ended hiatus at today — otherwise it would
     * cover every future date forever, suppressing the banner and the email
     * permanently and blocking any new hiatus in the overlap validation.
     * Dismissing the banner covers the "nothing needed adjusting" case, where
     * no transaction was created and the user would otherwise be nagged again
     * the very next day.
     */
    public function complete(User $user): void
    {
        $hiatus = $this->currentHiatus($user);

        if (!$hiatus instanceof Hiatus) {
            return;
        }

        $hiatus->update([
            'reconciled_at' => Date::now(),
            'ended_at'      => $hiatus->ended_at ?? Date::today(),
        ]);

        $this->inactivity->dismissBanner($user);
    }

    private function adjustmentCategory(User $user, TransactionType $type): Category
    {
        return $user->categories()->firstOrCreate(
            ['slug' => "adjustments-{$type->value}"],
            [
                'name'  => __('messages.reconciliation.adjustment_category', [], $user->locale),
                'type'  => $type,
                'color' => AccentColor::AMBER,
                'icon'  => CategoryIcon::BRIEFCASE,
            ],
        );
    }
}
