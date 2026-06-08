<?php

declare(strict_types=1);

use App\Enums\TransactionType;
use App\Models\Category;
use App\Models\SavingsBucket;
use App\Models\Transaction;
use App\Models\User;
use App\Services\SavingsRateService;
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
    $this->service = new SavingsRateService();
});

it('returns null rate when there is no income in the month', function (): void {
    $result = $this->service->forMonth($this->user, Date::create(2026, 5, 9));

    expect($result)->toBe([
        'deposits' => 0,
        'income'   => 0,
        'rate'     => null,
    ]);
});

it('computes deposits / income rate, ignoring withdraws on the income side', function (): void {
    $bucket = SavingsBucket::factory()->create(['user_id' => $this->user->id, 'current_amount' => 0]);

    Transaction::factory()->create([
        'user_id'          => $this->user->id,
        'category_id'      => $this->incomeCategory->id,
        'amount'           => 1000000,
        'type'             => TransactionType::INCOME->value,
        'transaction_date' => '2026-05-02',
    ]);

    Transaction::factory()->create([
        'user_id'           => $this->user->id,
        'category_id'       => $this->expenseCategory->id,
        'savings_bucket_id' => $bucket->id,
        'amount'            => 250000,
        'type'              => TransactionType::EXPENSE->value,
        'transaction_date'  => '2026-05-05',
    ]);

    Transaction::factory()->create([
        'user_id'           => $this->user->id,
        'category_id'       => $this->incomeCategory->id,
        'savings_bucket_id' => $bucket->id,
        'amount'            => 100000,
        'type'              => TransactionType::INCOME->value,
        'transaction_date'  => '2026-05-07',
    ]);

    $result = $this->service->forMonth($this->user, Date::create(2026, 5, 9));

    expect($result['deposits'])->toBe(250000);
    expect($result['income'])->toBe(1000000);
    expect($result['rate'])->toBe(25.0);
});

it('scopes the calculation to the requested month', function (): void {
    $bucket = SavingsBucket::factory()->create(['user_id' => $this->user->id, 'current_amount' => 0]);

    Transaction::factory()->create([
        'user_id'          => $this->user->id,
        'category_id'      => $this->incomeCategory->id,
        'amount'           => 500000,
        'type'             => TransactionType::INCOME->value,
        'transaction_date' => '2026-04-15',
    ]);

    Transaction::factory()->create([
        'user_id'           => $this->user->id,
        'category_id'       => $this->expenseCategory->id,
        'savings_bucket_id' => $bucket->id,
        'amount'            => 100000,
        'type'              => TransactionType::EXPENSE->value,
        'transaction_date'  => '2026-04-20',
    ]);

    expect($this->service->forMonth($this->user, Date::create(2026, 5, 1)))->toBe([
        'deposits' => 0,
        'income'   => 0,
        'rate'     => null,
    ]);

    expect($this->service->forMonth($this->user, Date::create(2026, 4, 1))['rate'])->toBe(20.0);
});
