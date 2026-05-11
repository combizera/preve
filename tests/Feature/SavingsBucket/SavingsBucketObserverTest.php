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

it('increments bucket balance on deposit (EXPENSE with bucket)', function (): void {
    $bucket = SavingsBucket::factory()->create(['user_id' => $this->user->id, 'current_amount' => 0]);

    Transaction::factory()->create([
        'user_id'           => $this->user->id,
        'category_id'       => $this->expenseCategory->id,
        'savings_bucket_id' => $bucket->id,
        'amount'            => 50000,
        'type'              => TransactionType::EXPENSE->value,
    ]);

    expect($bucket->fresh()->current_amount)->toBe(50000);
});

it('decrements bucket balance on withdraw (INCOME with bucket)', function (): void {
    $bucket = SavingsBucket::factory()->create(['user_id' => $this->user->id, 'current_amount' => 80000]);

    Transaction::factory()->create([
        'user_id'           => $this->user->id,
        'category_id'       => $this->incomeCategory->id,
        'savings_bucket_id' => $bucket->id,
        'amount'            => 30000,
        'type'              => TransactionType::INCOME->value,
    ]);

    expect($bucket->fresh()->current_amount)->toBe(50000);
});

it('reverses bucket balance on transaction delete', function (): void {
    $bucket = SavingsBucket::factory()->create(['user_id' => $this->user->id, 'current_amount' => 0]);

    $transaction = Transaction::factory()->create([
        'user_id'           => $this->user->id,
        'category_id'       => $this->expenseCategory->id,
        'savings_bucket_id' => $bucket->id,
        'amount'            => 25000,
        'type'              => TransactionType::EXPENSE->value,
    ]);

    expect($bucket->fresh()->current_amount)->toBe(25000);

    $transaction->delete();

    expect($bucket->fresh()->current_amount)->toBe(0);
});

it('moves balance when transaction switches bucket', function (): void {
    $bucketA = SavingsBucket::factory()->create(['user_id' => $this->user->id, 'current_amount' => 0]);
    $bucketB = SavingsBucket::factory()->create(['user_id' => $this->user->id, 'current_amount' => 0]);

    $transaction = Transaction::factory()->create([
        'user_id'           => $this->user->id,
        'category_id'       => $this->expenseCategory->id,
        'savings_bucket_id' => $bucketA->id,
        'amount'            => 10000,
        'type'              => TransactionType::EXPENSE->value,
    ]);

    $transaction->update(['savings_bucket_id' => $bucketB->id]);

    expect($bucketA->fresh()->current_amount)->toBe(0);
    expect($bucketB->fresh()->current_amount)->toBe(10000);
});

it('adjusts balance when transaction amount changes on the same bucket', function (): void {
    $bucket = SavingsBucket::factory()->create(['user_id' => $this->user->id, 'current_amount' => 0]);

    $transaction = Transaction::factory()->create([
        'user_id'           => $this->user->id,
        'category_id'       => $this->expenseCategory->id,
        'savings_bucket_id' => $bucket->id,
        'amount'            => 10000,
        'type'              => TransactionType::EXPENSE->value,
    ]);

    $transaction->update(['amount' => 7500]);

    expect($bucket->fresh()->current_amount)->toBe(7500);
});

it('does not touch any bucket when savings_bucket_id is null', function (): void {
    $bucket = SavingsBucket::factory()->create(['user_id' => $this->user->id, 'current_amount' => 12345]);

    Transaction::factory()->create([
        'user_id'     => $this->user->id,
        'category_id' => $this->expenseCategory->id,
        'amount'      => 50000,
        'type'        => TransactionType::EXPENSE->value,
    ]);

    expect($bucket->fresh()->current_amount)->toBe(12345);
});
