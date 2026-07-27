<?php

declare(strict_types=1);

use App\Enums\TransactionType;
use App\Models\Category;
use App\Models\Hiatus;
use App\Models\SavingsBucket;
use App\Models\Transaction;
use App\Models\User;

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

it('creates a deposit adjustment when the real bucket amount is higher', function (): void {
    $bucket = SavingsBucket::factory()->create([
        'user_id'        => $this->user->id,
        'current_amount' => 10000,
    ]);

    $this->post(route('reconciliation.buckets'), [
        'buckets' => [['id' => $bucket->id, 'real_amount' => 15000]],
    ])->assertRedirect(route('reconciliation.show'));

    $this->assertDatabaseHas('transactions', [
        'user_id'           => $this->user->id,
        'savings_bucket_id' => $bucket->id,
        'type'              => TransactionType::EXPENSE->value,
        'amount'            => 5000,
    ]);

    expect($bucket->refresh()->current_amount)->toBe(15000);
});

it('creates a withdraw adjustment when the real bucket amount is lower', function (): void {
    $bucket = SavingsBucket::factory()->create([
        'user_id'        => $this->user->id,
        'current_amount' => 10000,
    ]);

    $this->post(route('reconciliation.buckets'), [
        'buckets' => [['id' => $bucket->id, 'real_amount' => 4000]],
    ])->assertRedirect(route('reconciliation.show'));

    $this->assertDatabaseHas('transactions', [
        'savings_bucket_id' => $bucket->id,
        'type'              => TransactionType::INCOME->value,
        'amount'            => 6000,
    ]);

    expect($bucket->refresh()->current_amount)->toBe(4000);
});

it('creates no transaction when the bucket amount matches', function (): void {
    $bucket = SavingsBucket::factory()->create([
        'user_id'        => $this->user->id,
        'current_amount' => 10000,
    ]);

    $this->post(route('reconciliation.buckets'), [
        'buckets' => [['id' => $bucket->id, 'real_amount' => 10000]],
    ])->assertRedirect(route('reconciliation.show'));

    expect(Transaction::query()->count())->toBe(0);
});

it("rejects adjusting another user's bucket", function (): void {
    $foreign = SavingsBucket::factory()->create(['current_amount' => 10000]);

    $this->post(route('reconciliation.buckets'), [
        'buckets' => [['id' => $foreign->id, 'real_amount' => 0]],
    ])->assertSessionHasErrors('buckets.0.id');

    expect(Transaction::query()->count())->toBe(0);
});

it('creates an income adjustment when the real balance is higher', function (): void {
    Transaction::factory()->create([
        'user_id'          => $this->user->id,
        'type'             => TransactionType::INCOME,
        'amount'           => 100000,
        'transaction_date' => now()->subDays(40),
    ]);

    $this->post(route('reconciliation.balance'), [
        'real_balance' => 130000,
    ])->assertRedirect(route('reconciliation.show'));

    $this->assertDatabaseHas('transactions', [
        'user_id' => $this->user->id,
        'type'    => TransactionType::INCOME->value,
        'amount'  => 30000,
    ]);

    $this->assertDatabaseHas('categories', [
        'user_id' => $this->user->id,
        'slug'    => 'adjustments-income',
    ]);
});

it('creates an expense adjustment when the real balance is lower', function (): void {
    Transaction::factory()->create([
        'user_id'          => $this->user->id,
        'type'             => TransactionType::INCOME,
        'amount'           => 100000,
        'transaction_date' => now()->subDays(40),
    ]);

    $this->post(route('reconciliation.balance'), [
        'real_balance' => 75000,
    ])->assertRedirect(route('reconciliation.show'));

    $this->assertDatabaseHas('transactions', [
        'type'   => TransactionType::EXPENSE->value,
        'amount' => 25000,
    ]);
});

it('creates no adjustment when the balance matches', function (): void {
    Transaction::factory()->create([
        'user_id'          => $this->user->id,
        'type'             => TransactionType::INCOME,
        'amount'           => 100000,
        'transaction_date' => now()->subDays(40),
    ]);

    $this->post(route('reconciliation.balance'), [
        'real_balance' => 100000,
    ])->assertRedirect(route('reconciliation.show'));

    expect(Transaction::query()->count())->toBe(1);
});

it('reuses the adjustment category on repeated adjustments', function (): void {
    $this->post(route('reconciliation.balance'), ['real_balance' => 5000]);
    $this->post(route('reconciliation.balance'), ['real_balance' => 9000]);

    expect(
        Category::query()
            ->where('user_id', $this->user->id)
            ->where('slug', 'adjustments-income')
            ->count(),
    )->toBe(1);
});

it('marks the current hiatus as reconciled on completion', function (): void {
    $hiatus = Hiatus::factory()->create([
        'user_id'    => $this->user->id,
        'started_at' => now()->subDays(60)->toDateString(),
        'ended_at'   => now()->toDateString(),
    ]);

    $this->post(route('reconciliation.complete'))
        ->assertRedirect(route('dashboard'));

    expect($hiatus->refresh()->reconciled_at)->not->toBeNull();
});

it('caps an open-ended hiatus at today on completion', function (): void {
    $hiatus = Hiatus::factory()->open()->create([
        'user_id'    => $this->user->id,
        'started_at' => now()->subDays(60)->toDateString(),
    ]);

    $this->post(route('reconciliation.complete'))
        ->assertRedirect(route('dashboard'));

    $hiatus->refresh();

    expect($hiatus->reconciled_at)->not->toBeNull()
        ->and($hiatus->ended_at?->toDateString())->toBe(now()->toDateString());
});

it('dismisses the banner on completion even without adjustments', function (): void {
    Hiatus::factory()->create([
        'user_id'    => $this->user->id,
        'started_at' => now()->subDays(60)->toDateString(),
        'ended_at'   => now()->toDateString(),
    ]);

    $this->post(route('reconciliation.complete'));

    expect($this->user->refresh()->reengagement_banner_dismissed_at)->not->toBeNull();
});

it('ignores a future-dated hiatus in the wizard', function (): void {
    Hiatus::factory()->create([
        'user_id'    => $this->user->id,
        'started_at' => now()->addDays(10)->toDateString(),
        'ended_at'   => now()->addDays(30)->toDateString(),
    ]);

    $this->get(route('reconciliation.show'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->where('hiatus', null));
});

it('completes gracefully without a hiatus', function (): void {
    $this->post(route('reconciliation.complete'))
        ->assertRedirect(route('dashboard'));
});

it('shares the current balance and buckets in the wizard payload', function (): void {
    SavingsBucket::factory()->create(['user_id' => $this->user->id]);
    Transaction::factory()->create([
        'user_id'          => $this->user->id,
        'type'             => TransactionType::INCOME,
        'amount'           => 100000,
        'transaction_date' => now()->subDays(40),
    ]);

    $this->get(route('reconciliation.show'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('currentBalance', 100000)
            ->has('savingsBuckets', 1)
        );
});
