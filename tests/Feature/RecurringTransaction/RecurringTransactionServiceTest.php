<?php

declare(strict_types=1);

use App\Models\RecurringTransaction;
use App\Models\User;
use App\Services\RecurringTransactionService;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Log;

beforeEach(function (): void {
    $this->service = new RecurringTransactionService();
    $this->user = User::factory()->create();
});

// GENERATION
it('should be able to generate future transactions and avoid duplicates', function (): void {
    Date::setTestNow('2026-03-01');

    $recurring = RecurringTransaction::factory()->create([
        'user_id'      => $this->user->id,
        'start_date'   => Date::parse('2026-03-01'),
        'day_of_month' => 10,
        'is_active'    => true,
        'end_date'     => null,
    ]);

    $this->service->generateFutureTransactions($recurring, 3);
    expect($recurring->transactions()->count())->toBe(4);

    $this->service->generateFutureTransactions($recurring, 3);
    expect($recurring->transactions()->count())->toBe(4);
});

it('should not be able to generate transactions for inactive templates', function (): void {
    $recurring = RecurringTransaction::factory()->create([
        'user_id'   => $this->user->id,
        'is_active' => false,
    ]);

    $this->service->generateFutureTransactions($recurring, 3);
    expect($recurring->transactions()->count())->toBe(0);
});

it('should not be able to generate transactions beyond the end_date limit', function (): void {
    Date::setTestNow('2026-03-01');

    $recurring = RecurringTransaction::factory()->create([
        'user_id'      => $this->user->id,
        'start_date'   => Date::parse('2026-03-01'),
        'end_date'     => Date::parse('2026-04-30'),
        'day_of_month' => 15,
        'is_active'    => true,
    ]);

    $this->service->generateFutureTransactions($recurring, 3);

    expect($recurring->transactions()->count())->toBe(2);
});

// LOGGING
it('should log a success message when a transaction is generated', function (): void {
    Log::spy();

    Date::setTestNow('2026-03-01');

    $recurring = RecurringTransaction::factory()->create([
        'user_id'      => $this->user->id,
        'start_date'   => Date::parse('2026-03-01'),
        'day_of_month' => 10,
        'is_active'    => true,
    ]);

    $this->service->generateFutureTransactions($recurring, 0);

    Log::shouldHaveReceived('info')
        ->once()
        ->withArgs(fn ($message, array $context): bool => $message === 'Recurring transaction generated successfully.'
            && $context['recurring_id'] === $recurring->id);
});

it('should log an error message when transaction generation fails', function (): void {
    Log::spy();

    Date::setTestNow('2026-03-01');

    $recurring = RecurringTransaction::factory()->create([
        'user_id'      => $this->user->id,
        'start_date'   => Date::parse('2026-03-01'),
        'day_of_month' => 10,
        'is_active'    => true,
    ]);

    $recurring->category_id = 9999999;

    $this->service->generateFutureTransactions($recurring, 0);

    Log::shouldHaveReceived('error')
        ->once()
        ->withArgs(fn ($message, array $context): bool => $message === 'Failed to generate recurring transaction.'
            && $context['recurring_id'] === $recurring->id
            && isset($context['error']));
});
