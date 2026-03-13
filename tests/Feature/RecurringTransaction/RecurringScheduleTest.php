<?php

declare(strict_types=1);

use App\Console\Commands\GenerateRecurringTransactions;
use App\Jobs\GenerateRecurringTransactionsJob;
use App\Models\RecurringTransaction;
use App\Models\User;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Queue;

// DISPATCH
it('should be able to dispatch jobs only for active recurring transactions', function (): void {
    Queue::fake();

    $user = User::factory()->create();

    $active = RecurringTransaction::factory()->create(['user_id' => $user->id, 'is_active' => true]);
    $inactive = RecurringTransaction::factory()->create(['user_id' => $user->id, 'is_active' => false]);

    Artisan::call('recurring:generate', ['--months' => 3]);

    Queue::assertPushed(GenerateRecurringTransactionsJob::class, fn ($job): bool => $job->recurringTransaction->id === $active->id && $job->monthsAhead === 3);

    Queue::assertNotPushed(GenerateRecurringTransactionsJob::class, fn ($job): bool => $job->recurringTransaction->id === $inactive->id);
});

// SCHEDULE
it('should be able to verify that the weekly schedule is properly configured', function (): void {
    $schedule = app()->make(Schedule::class);

    $commandName = resolve(GenerateRecurringTransactions::class)->getName();

    $events = collect($schedule->events())->filter(fn ($event): bool => mb_stripos((string) $event->command, "{$commandName} --months=3") !== false);

    expect($events->count())->toBe(1);

    $event = $events->first();
    expect($event->expression)->toBe('0 0 * * 0');
});
