<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Transaction;
use App\Services\SavingsBucketBalanceService;

final readonly class TransactionObserver
{
    public function __construct(private SavingsBucketBalanceService $balance) {}

    public function created(Transaction $transaction): void
    {
        $this->balance->applyForCreated($transaction);
    }

    public function updated(Transaction $transaction): void
    {
        $this->balance->applyForUpdated($transaction);
    }

    public function deleted(Transaction $transaction): void
    {
        $this->balance->applyForDeleted($transaction);
    }
}
