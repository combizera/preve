<?php

declare(strict_types=1);

use App\Enums\TransactionType;
use App\Models\Category;
use App\Models\SavingsBucket;
use App\Models\Transaction;
use App\Models\User;
use App\Services\SavingsBucketBalanceService;
use App\Services\SavingsHistoryService;
use Illuminate\Support\Facades\Date;

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->expenseCategory = Category::factory()->create([
        'user_id' => $this->user->id,
        'type'    => TransactionType::EXPENSE->value,
    ]);
    $this->incomeCategory = Category::factory()->create([
        'user_id' => $this->user->id,
        'type'    => TransactionType::INCOME->value,
    ]);
    $this->service = new SavingsHistoryService(new SavingsBucketBalanceService());
});

it('returns 12 zeroed months when the user has no savings activity', function (): void {
    $result = $this->service->monthlyBalances($this->user, 2026);

    expect($result)->toHaveCount(12);
    expect($result[0])->toBe([
        'month'       => '2026-01',
        'balance'     => 0,
        'deposits'    => 0,
        'withdrawals' => 0,
    ]);
    expect(array_sum(array_column($result, 'balance')))->toBe(0);
});

it("treats a bucket's initial amount as a deposit on its creation month", function (): void {
    Date::setTestNow('2026-03-15 12:00:00');

    SavingsBucket::factory()->create([
        'user_id'        => $this->user->id,
        'current_amount' => 500000,
    ]);

    $result = $this->service->monthlyBalances($this->user, 2026);

    expect($result[1])->toBe([
        'month'       => '2026-02',
        'balance'     => 0,
        'deposits'    => 0,
        'withdrawals' => 0,
    ]);
    expect($result[2])->toBe([
        'month'       => '2026-03',
        'balance'     => 500000,
        'deposits'    => 500000,
        'withdrawals' => 0,
    ]);
    expect($result[11]['balance'])->toBe(500000);
});

it('accumulates deposits and withdrawals across the year', function (): void {
    Date::setTestNow('2026-06-01 12:00:00');

    $bucket = SavingsBucket::factory()->create([
        'user_id'        => $this->user->id,
        'current_amount' => 0,
    ]);

    Transaction::factory()->create([
        'user_id'           => $this->user->id,
        'category_id'       => $this->expenseCategory->id,
        'savings_bucket_id' => $bucket->id,
        'amount'            => 100000,
        'type'              => TransactionType::EXPENSE->value,
        'transaction_date'  => '2026-02-10',
    ]);

    Transaction::factory()->create([
        'user_id'           => $this->user->id,
        'category_id'       => $this->expenseCategory->id,
        'savings_bucket_id' => $bucket->id,
        'amount'            => 50000,
        'type'              => TransactionType::EXPENSE->value,
        'transaction_date'  => '2026-04-05',
    ]);

    Transaction::factory()->create([
        'user_id'           => $this->user->id,
        'category_id'       => $this->incomeCategory->id,
        'savings_bucket_id' => $bucket->id,
        'amount'            => 30000,
        'type'              => TransactionType::INCOME->value,
        'transaction_date'  => '2026-05-20',
    ]);

    $result = $this->service->monthlyBalances($this->user, 2026);

    expect($result[0]['balance'])->toBe(0);
    expect($result[1]['balance'])->toBe(100000);
    expect($result[1]['deposits'])->toBe(100000);
    expect($result[2]['balance'])->toBe(100000);
    expect($result[3]['balance'])->toBe(150000);
    expect($result[4]['balance'])->toBe(120000);
    expect($result[4]['withdrawals'])->toBe(30000);
    expect($result[11]['balance'])->toBe(120000);
});

it('ignores buckets and transactions from other users', function (): void {
    $other = User::factory()->create();
    $otherBucket = SavingsBucket::factory()->create([
        'user_id'        => $other->id,
        'current_amount' => 99999,
    ]);

    Transaction::factory()->create([
        'user_id'           => $other->id,
        'category_id'       => Category::factory()->create(['user_id' => $other->id])->id,
        'savings_bucket_id' => $otherBucket->id,
        'amount'            => 99999,
        'type'              => TransactionType::EXPENSE->value,
        'transaction_date'  => '2026-01-15',
    ]);

    $result = $this->service->monthlyBalances($this->user, 2026);

    expect(array_sum(array_column($result, 'balance')))->toBe(0);
});

it('lists available years from earliest activity through the current year', function (): void {
    Date::setTestNow('2026-05-15 12:00:00');

    SavingsBucket::factory()->create([
        'user_id'    => $this->user->id,
        'created_at' => '2024-08-01 10:00:00',
    ]);

    expect($this->service->availableYears($this->user))->toBe([2026, 2025, 2024]);
});

it('falls back to the current year when there is no activity', function (): void {
    Date::setTestNow('2026-05-15 12:00:00');

    expect($this->service->availableYears($this->user))->toBe([2026]);
});
