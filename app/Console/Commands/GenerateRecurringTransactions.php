<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Jobs\GenerateRecurringTransactionsJob;
use App\Models\RecurringTransaction;
use Illuminate\Console\Command;

final class GenerateRecurringTransactions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'recurring:generate {--months=3: Number of future months to be generated}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'It generates future transactions based on active recurring models.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $months = (int) $this->option('months');
        $this->info("Starting transaction generation for the next {$months} months...");

        RecurringTransaction::where('is_active', true)
            ->chunkById(100, function ($recurringTransactions) use ($months) {
                foreach ($recurringTransactions as $recurringTransaction) {
                    GenerateRecurringTransactionsJob::dispatch($recurringTransaction, $months);
                }
            });

        $this->info('Processing successfully sent to the queue.!');
    }
}
