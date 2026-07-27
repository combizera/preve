<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Transaction;
use App\Models\User;
use App\Services\InactivityService;
use App\Services\SavingsBucketBalanceService;

final readonly class TransactionObserver
{
    public function __construct(
        private SavingsBucketBalanceService $balance,
        private InactivityService $inactivity,
    ) {}

    public function created(Transaction $transaction): void
    {
        $this->balance->applyForCreated($transaction);
        $this->forgetInactivityCache($transaction);

        if ($transaction->recurring_transaction_id === null) {
            User::query()->whereKey($transaction->user_id)
                ->whereNotNull('reengagement_notified_at')
                ->update(['reengagement_notified_at' => null]);
        }
    }

    public function updated(Transaction $transaction): void
    {
        $this->balance->applyForUpdated($transaction);
    }

    public function deleted(Transaction $transaction): void
    {
        $this->balance->applyForDeleted($transaction);
        $this->forgetInactivityCache($transaction);
    }

    private function forgetInactivityCache(Transaction $transaction): void
    {
        if ($transaction->recurring_transaction_id === null) {
            $this->inactivity->forgetLastManualActivity($transaction->user_id);
        }
    }
}
