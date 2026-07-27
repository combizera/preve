<?php

declare(strict_types=1);

use App\Models\Hiatus;
use App\Models\RecurringTransaction;
use App\Models\Transaction;
use App\Models\User;
use App\Services\InactivityService;

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->service = new InactivityService();
});

it('returns null last manual activity for a user without transactions', function (): void {
    expect($this->service->lastManualActivity($this->user))->toBeNull();
});

it('ignores recurring-generated transactions as activity', function (): void {
    $recurring = RecurringTransaction::factory()->create(['user_id' => $this->user->id]);
    Transaction::factory()->create([
        'user_id'                  => $this->user->id,
        'recurring_transaction_id' => $recurring->id,
        'created_at'               => now()->subDays(2),
    ]);

    expect($this->service->lastManualActivity($this->user))->toBeNull()
        ->and($this->service->isInactive($this->user))->toBeFalse();
});

it('returns the latest manual transaction as last activity', function (): void {
    Transaction::factory()->create([
        'user_id'    => $this->user->id,
        'created_at' => now()->subDays(50),
    ]);
    Transaction::factory()->create([
        'user_id'    => $this->user->id,
        'created_at' => now()->subDays(10),
    ]);

    expect($this->service->inactiveDays($this->user))->toBe(10);
});

it('marks a user inactive after the threshold', function (): void {
    Transaction::factory()->create([
        'user_id'    => $this->user->id,
        'created_at' => now()->subDays(31),
    ]);

    expect($this->service->isInactive($this->user))->toBeTrue();
});

it('does not mark a user with recent manual activity as inactive', function (): void {
    Transaction::factory()->create([
        'user_id'    => $this->user->id,
        'created_at' => now()->subDays(5),
    ]);

    expect($this->service->isInactive($this->user))->toBeFalse();
});

it('respects a custom threshold', function (): void {
    Transaction::factory()->create([
        'user_id'    => $this->user->id,
        'created_at' => now()->subDays(8),
    ]);

    expect($this->service->isInactive($this->user, 7))->toBeTrue()
        ->and($this->service->isInactive($this->user, 14))->toBeFalse();
});

it('detects an active hiatus covering today', function (): void {
    Hiatus::factory()->create([
        'user_id'    => $this->user->id,
        'started_at' => now()->subDays(10)->toDateString(),
        'ended_at'   => now()->addDays(10)->toDateString(),
    ]);

    expect($this->service->hasActiveHiatus($this->user))->toBeTrue();
});

it('detects an open-ended hiatus as active', function (): void {
    Hiatus::factory()->open()->create([
        'user_id'    => $this->user->id,
        'started_at' => now()->subDays(10)->toDateString(),
    ]);

    expect($this->service->hasActiveHiatus($this->user))->toBeTrue();
});

it('does not treat a past hiatus as active', function (): void {
    Hiatus::factory()->create([
        'user_id'    => $this->user->id,
        'started_at' => now()->subDays(60)->toDateString(),
        'ended_at'   => now()->subDays(30)->toDateString(),
    ]);

    expect($this->service->hasActiveHiatus($this->user))->toBeFalse();
});

it('segments only genuinely inactive users', function (): void {
    $inactive = User::factory()->create();
    Transaction::factory()->create([
        'user_id'    => $inactive->id,
        'created_at' => now()->subDays(45),
    ]);

    $active = User::factory()->create();
    Transaction::factory()->create([
        'user_id'    => $active->id,
        'created_at' => now()->subDays(3),
    ]);

    $neverCreated = User::factory()->create();

    $onlyRecurring = User::factory()->create();
    $recurring = RecurringTransaction::factory()->create(['user_id' => $onlyRecurring->id]);
    Transaction::factory()->create([
        'user_id'                  => $onlyRecurring->id,
        'recurring_transaction_id' => $recurring->id,
        'created_at'               => now()->subDays(2),
    ]);

    $inactiveDespiteRecurring = User::factory()->create();
    Transaction::factory()->create([
        'user_id'    => $inactiveDespiteRecurring->id,
        'created_at' => now()->subDays(45),
    ]);
    $recurring2 = RecurringTransaction::factory()->create(['user_id' => $inactiveDespiteRecurring->id]);
    Transaction::factory()->create([
        'user_id'                  => $inactiveDespiteRecurring->id,
        'recurring_transaction_id' => $recurring2->id,
        'created_at'               => now()->subDays(1),
    ]);

    $ids = $this->service->inactiveUsersQuery()->pluck('id');

    expect($ids)->toContain($inactive->id)
        ->toContain($inactiveDespiteRecurring->id)
        ->not->toContain($active->id)
        ->not->toContain($neverCreated->id)
        ->not->toContain($onlyRecurring->id);
});
