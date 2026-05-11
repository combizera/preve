<?php

declare(strict_types=1);

use App\Enums\TransactionType;
use App\Models\Category;
use App\Models\SavingsBucket;
use App\Models\Transaction;
use App\Models\User;

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);

    $this->expenseCategory = Category::factory()->create([
        'user_id' => $this->user->id,
        'type'    => TransactionType::EXPENSE->value,
    ]);

    $this->incomeCategory = Category::factory()->create([
        'user_id' => $this->user->id,
        'type'    => TransactionType::INCOME->value,
    ]);
});

it('stores a transaction with savings_bucket_id and accrues the bucket', function (): void {
    $bucket = SavingsBucket::factory()->create([
        'user_id'        => $this->user->id,
        'current_amount' => 0,
    ]);

    $this->post(route('transactions.store'), [
        'category_id'       => $this->expenseCategory->id,
        'savings_bucket_id' => $bucket->id,
        'amount'            => 50000,
        'type'              => TransactionType::EXPENSE->value,
        'description'       => 'Aporte mensal',
        'transaction_date'  => '2026-05-09',
    ])->assertRedirectBack();

    $this->assertDatabaseHas('transactions', [
        'user_id'           => $this->user->id,
        'savings_bucket_id' => $bucket->id,
        'description'       => 'Aporte mensal',
    ]);

    expect($bucket->fresh()->current_amount)->toBe(50000);
});

it('rejects savings_bucket_id belonging to another user', function (): void {
    $otherBucket = SavingsBucket::factory()->create();

    $this->post(route('transactions.store'), [
        'category_id'       => $this->expenseCategory->id,
        'savings_bucket_id' => $otherBucket->id,
        'amount'            => 1000,
        'type'              => TransactionType::EXPENSE->value,
        'description'       => 'Sneaky deposit',
        'transaction_date'  => '2026-05-09',
    ])->assertSessionHasErrors('savings_bucket_id');

    expect($otherBucket->fresh()->current_amount)->toBe($otherBucket->current_amount);
});

it('updates a transaction to attach a bucket and recalculates balance', function (): void {
    $bucket = SavingsBucket::factory()->create([
        'user_id'        => $this->user->id,
        'current_amount' => 0,
    ]);

    $transaction = Transaction::factory()->create([
        'user_id'     => $this->user->id,
        'category_id' => $this->expenseCategory->id,
        'amount'      => 25000,
        'type'        => TransactionType::EXPENSE->value,
    ]);

    $this->put(route('transactions.update', $transaction->id), [
        'category_id'       => $this->expenseCategory->id,
        'savings_bucket_id' => $bucket->id,
        'amount'            => 25000,
        'type'              => TransactionType::EXPENSE->value,
        'description'       => 'Now with bucket',
        'transaction_date'  => '2026-05-09',
    ])->assertRedirectBack();

    expect($bucket->fresh()->current_amount)->toBe(25000);
});

it('rejects withdraw greater than current balance', function (): void {
    $bucket = SavingsBucket::factory()->create([
        'user_id'        => $this->user->id,
        'current_amount' => 10000,
    ]);

    $this->post(route('transactions.store'), [
        'category_id'       => $this->incomeCategory->id,
        'savings_bucket_id' => $bucket->id,
        'amount'            => 50000,
        'type'              => TransactionType::INCOME->value,
        'description'       => 'Big withdraw',
        'transaction_date'  => '2026-05-09',
    ])->assertSessionHasErrors('amount');

    expect($bucket->fresh()->current_amount)->toBe(10000);
});

it('allows updating amount on the same withdraw without false overdraw', function (): void {
    $bucket = SavingsBucket::factory()->create([
        'user_id'        => $this->user->id,
        'current_amount' => 30000,
    ]);

    $transaction = Transaction::factory()->create([
        'user_id'           => $this->user->id,
        'category_id'       => $this->incomeCategory->id,
        'savings_bucket_id' => $bucket->id,
        'amount'            => 20000,
        'type'              => TransactionType::INCOME->value,
    ]);

    $this->put(route('transactions.update', $transaction->id), [
        'category_id'       => $this->incomeCategory->id,
        'savings_bucket_id' => $bucket->id,
        'amount'            => 25000,
        'type'              => TransactionType::INCOME->value,
        'description'       => 'Withdraw resized',
        'transaction_date'  => '2026-05-09',
    ])->assertRedirectBack();

    expect($bucket->fresh()->current_amount)->toBe(5000);
});

it('renders the dashboard with savings rate and buckets', function (): void {
    SavingsBucket::factory()->create([
        'user_id'        => $this->user->id,
        'current_amount' => 100,
    ]);

    $response = $this->get(route('dashboard'));
    $response->assertOk();
});
