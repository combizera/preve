<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\TransactionType;
use App\Models\SavingsBucket;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

/**
 * Pure domain rules for how a Transaction moves money in/out of a SavingsBucket:
 * EXPENSE + bucket = deposit (+), INCOME + bucket = withdraw (-).
 */
final class SavingsBucketBalanceService
{
    public function bucketDelta(int $amount, TransactionType $type): int
    {
        return $type === TransactionType::EXPENSE ? $amount : -$amount;
    }

    public function applyForCreated(Transaction $transaction): void
    {
        if ($transaction->savings_bucket_id === null) {
            return;
        }

        $this->apply(
            (int) $transaction->savings_bucket_id,
            $this->deltaFor($transaction),
        );
    }

    public function applyForUpdated(Transaction $transaction): void
    {
        $oldBucketId = $transaction->getOriginal('savings_bucket_id');
        $newBucketId = $transaction->savings_bucket_id;

        if ($oldBucketId !== null) {
            $this->apply((int) $oldBucketId, -$this->originalDeltaFor($transaction));
        }

        if ($newBucketId !== null) {
            $this->apply((int) $newBucketId, $this->deltaFor($transaction));
        }
    }

    public function applyForDeleted(Transaction $transaction): void
    {
        if ($transaction->savings_bucket_id === null) {
            return;
        }

        $this->apply(
            (int) $transaction->savings_bucket_id,
            -$this->deltaFor($transaction),
        );
    }

    /**
     * Decides whether a prospective transaction would push the bucket below zero.
     * When updating an existing transaction tied to the same bucket, the original
     * contribution is excluded from the baseline so the comparison is apples-to-apples.
     */
    public function wouldOverdraw(
        SavingsBucket $bucket,
        int $amount,
        TransactionType $type,
        ?Transaction $excluding = null,
    ): bool {
        $newDelta = $this->bucketDelta($amount, $type);
        $baseline = (int) $bucket->current_amount;

        if ($excluding instanceof Transaction && (int) $excluding->savings_bucket_id === (int) $bucket->id) {
            $baseline -= $this->deltaFor($excluding);
        }

        return $baseline + $newDelta < 0;
    }

    private function deltaFor(Transaction $transaction): int
    {
        return $this->bucketDelta(
            (int) $transaction->amount,
            $this->resolveType($transaction->type),
        );
    }

    private function originalDeltaFor(Transaction $transaction): int
    {
        return $this->bucketDelta(
            (int) $transaction->getOriginal('amount'),
            $this->resolveType($transaction->getOriginal('type')),
        );
    }

    private function resolveType(mixed $type): TransactionType
    {
        return $type instanceof TransactionType
            ? $type
            : TransactionType::from((string) $type);
    }

    private function apply(int $bucketId, int $delta): void
    {
        if ($delta === 0) {
            return;
        }

        SavingsBucket::query()
            ->whereKey($bucketId)
            ->update(['current_amount' => DB::raw('current_amount + ' . $delta)]);
    }
}
