<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\RecurringTransaction;
use App\Services\RecurringTransactionService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

final class GenerateRecurringTransactionsJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public RecurringTransaction $recurringTransaction,
        public int $monthsAhead
    ) {}

    public function handle(RecurringTransactionService $service): void
    {
        $service->generateFutureTransactions($this->recurringTransaction, $this->monthsAhead);
    }
}
