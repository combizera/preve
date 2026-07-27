<?php

declare(strict_types=1);

use App\Models\Hiatus;
use App\Models\RecurringTransaction;
use App\Models\Transaction;
use App\Models\User;

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

function makeHiatus(User $user): Hiatus
{
    return Hiatus::factory()->create([
        'user_id'    => $user->id,
        'started_at' => now()->subDays(60)->toDateString(),
        'ended_at'   => now()->toDateString(),
    ]);
}

function makeGeneratedTransaction(User $user, string $date): Transaction
{
    $recurring = RecurringTransaction::factory()->create(['user_id' => $user->id]);

    return Transaction::factory()->create([
        'user_id'                  => $user->id,
        'recurring_transaction_id' => $recurring->id,
        'transaction_date'         => $date,
    ]);
}

it('renders the wizard without a hiatus suggesting the detected period', function (): void {
    Transaction::factory()->create([
        'user_id'    => $this->user->id,
        'created_at' => now()->subDays(45),
    ]);

    $this->get(route('reconciliation.show'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Reconciliation')
            ->where('hiatus', null)
            ->where('suggestedStart', now()->subDays(45)->toDateString())
        );
});

it('lists only recurring-generated transactions within the hiatus period', function (): void {
    makeHiatus($this->user);

    $inside = makeGeneratedTransaction($this->user, now()->subDays(30)->toDateString());
    makeGeneratedTransaction($this->user, now()->subDays(90)->toDateString());
    Transaction::factory()->create([
        'user_id'          => $this->user->id,
        'transaction_date' => now()->subDays(30)->toDateString(),
    ]);

    $this->get(route('reconciliation.show'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('pendingRecurring', 1)
            ->where('pendingRecurring.0.id', $inside->id)
        );
});

it('deletes the selected generated transactions and keeps the rest', function (): void {
    makeHiatus($this->user);

    $remove = makeGeneratedTransaction($this->user, now()->subDays(30)->toDateString());
    $keep = makeGeneratedTransaction($this->user, now()->subDays(20)->toDateString());

    $this->post(route('reconciliation.recurring'), [
        'delete_transaction_ids' => [$remove->id],
    ])->assertRedirect(route('reconciliation.show'));

    $this->assertDatabaseMissing('transactions', ['id' => $remove->id]);
    $this->assertDatabaseHas('transactions', ['id' => $keep->id]);
});

it('rejects deleting manually created transactions', function (): void {
    makeHiatus($this->user);

    $manual = Transaction::factory()->create(['user_id' => $this->user->id]);

    $this->post(route('reconciliation.recurring'), [
        'delete_transaction_ids' => [$manual->id],
    ])->assertSessionHasErrors('delete_transaction_ids.0');

    $this->assertDatabaseHas('transactions', ['id' => $manual->id]);
});

it("rejects deleting another user's transactions", function (): void {
    makeHiatus($this->user);

    $other = User::factory()->create();
    $foreign = makeGeneratedTransaction($other, now()->subDays(30)->toDateString());

    $this->post(route('reconciliation.recurring'), [
        'delete_transaction_ids' => [$foreign->id],
    ])->assertSessionHasErrors('delete_transaction_ids.0');

    $this->assertDatabaseHas('transactions', ['id' => $foreign->id]);
});

it('deactivates a recurring transaction and removes its future charges', function (): void {
    makeHiatus($this->user);

    $recurring = RecurringTransaction::factory()->create([
        'user_id'   => $this->user->id,
        'is_active' => true,
    ]);
    $future = Transaction::factory()->create([
        'user_id'                  => $this->user->id,
        'recurring_transaction_id' => $recurring->id,
        'transaction_date'         => now()->addMonths(2)->toDateString(),
    ]);
    $currentMonth = Transaction::factory()->create([
        'user_id'                  => $this->user->id,
        'recurring_transaction_id' => $recurring->id,
        'transaction_date'         => now()->startOfMonth()->toDateString(),
    ]);

    $this->post(route('reconciliation.recurring'), [
        'deactivate_recurring_ids' => [$recurring->id],
    ])->assertRedirect(route('reconciliation.show'));

    expect($recurring->refresh()->is_active)->toBeFalse();
    $this->assertDatabaseMissing('transactions', ['id' => $future->id]);
    $this->assertDatabaseHas('transactions', ['id' => $currentMonth->id]);
});

it('keeps current-month card charges when deactivating by competence date', function (): void {
    makeHiatus($this->user);

    $recurring = RecurringTransaction::factory()->create([
        'user_id'   => $this->user->id,
        'is_active' => true,
    ]);
    $cardChargeThisMonth = Transaction::factory()->create([
        'user_id'                  => $this->user->id,
        'recurring_transaction_id' => $recurring->id,
        'purchase_date'            => now()->startOfMonth()->toDateString(),
        'transaction_date'         => now()->addMonth()->toDateString(),
    ]);
    $cardChargeNextMonth = Transaction::factory()->create([
        'user_id'                  => $this->user->id,
        'recurring_transaction_id' => $recurring->id,
        'purchase_date'            => now()->addMonth()->startOfMonth()->toDateString(),
        'transaction_date'         => now()->addMonths(2)->toDateString(),
    ]);

    $this->post(route('reconciliation.recurring'), [
        'deactivate_recurring_ids' => [$recurring->id],
    ])->assertRedirect(route('reconciliation.show'));

    $this->assertDatabaseHas('transactions', ['id' => $cardChargeThisMonth->id]);
    $this->assertDatabaseMissing('transactions', ['id' => $cardChargeNextMonth->id]);
});

it("rejects deactivating another user's recurring transaction", function (): void {
    makeHiatus($this->user);

    $foreign = RecurringTransaction::factory()->create(['is_active' => true]);

    $this->post(route('reconciliation.recurring'), [
        'deactivate_recurring_ids' => [$foreign->id],
    ])->assertSessionHasErrors('deactivate_recurring_ids.0');

    expect($foreign->refresh()->is_active)->toBeTrue();
});
