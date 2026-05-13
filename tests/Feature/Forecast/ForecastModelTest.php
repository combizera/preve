<?php

declare(strict_types=1);

use App\Enums\ForecastPaceStatus;
use App\Enums\TransactionType;
use App\Models\Category;
use App\Models\Forecast;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\Date;

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->category = Category::factory()->create([
        'user_id' => $this->user->id,
        'type'    => TransactionType::EXPENSE->value,
    ]);
});

it('computes daily allowance via integer division', function (): void {
    $forecast = Forecast::factory()->create([
        'user_id'     => $this->user->id,
        'category_id' => $this->category->id,
        'amount'      => 90000,
        'month'       => '2026-04-01', // 30 days
    ]);

    expect($forecast->computeDailyAllowance())->toBe(3000);
});

it('sums only paid expenses for its own category and month', function (): void {
    $forecast = Forecast::factory()->create([
        'user_id'     => $this->user->id,
        'category_id' => $this->category->id,
        'amount'      => 100000,
        'month'       => '2026-04-01',
    ]);

    Transaction::factory()->create([
        'user_id'          => $this->user->id,
        'category_id'      => $this->category->id,
        'type'             => TransactionType::EXPENSE->value,
        'amount'           => 30000,
        'transaction_date' => '2026-04-05',
    ]);

    Transaction::factory()->create([
        'user_id'          => $this->user->id,
        'category_id'      => $this->category->id,
        'type'             => TransactionType::EXPENSE->value,
        'amount'           => 20000,
        'transaction_date' => '2026-04-25', // future relative to asOf
    ]);

    Transaction::factory()->create([
        'user_id'          => $this->user->id,
        'category_id'      => $this->category->id,
        'type'             => TransactionType::EXPENSE->value,
        'amount'           => 5000,
        'transaction_date' => '2026-03-30', // outside month
    ]);

    $otherCategory = Category::factory()->create([
        'user_id' => $this->user->id,
        'type'    => TransactionType::EXPENSE->value,
    ]);
    Transaction::factory()->create([
        'user_id'          => $this->user->id,
        'category_id'      => $otherCategory->id,
        'type'             => TransactionType::EXPENSE->value,
        'amount'           => 9999,
        'transaction_date' => '2026-04-10',
    ]);

    Transaction::factory()->create([
        'user_id'          => $this->user->id,
        'category_id'      => $this->category->id,
        'type'             => TransactionType::INCOME->value,
        'amount'           => 7777,
        'transaction_date' => '2026-04-12',
    ]);

    expect($forecast->computeSpentToDate(Date::parse('2026-04-15')))->toBe(30000);
});

it('reports OVER_PACE when actual spending is comfortably above expected', function (): void {
    $forecast = Forecast::factory()->create([
        'user_id'     => $this->user->id,
        'category_id' => $this->category->id,
        'amount'      => 100000,
        'month'       => '2026-04-01',
    ]);

    Transaction::factory()->create([
        'user_id'          => $this->user->id,
        'category_id'      => $this->category->id,
        'type'             => TransactionType::EXPENSE->value,
        'amount'           => 80000,
        'transaction_date' => '2026-04-10',
    ]);

    expect($forecast->computePaceStatus(Date::parse('2026-04-15')))->toBe(ForecastPaceStatus::OVER_PACE);
});

it('reports UNDER_PACE when actual spending is comfortably below expected', function (): void {
    $forecast = Forecast::factory()->create([
        'user_id'     => $this->user->id,
        'category_id' => $this->category->id,
        'amount'      => 100000,
        'month'       => '2026-04-01',
    ]);

    Transaction::factory()->create([
        'user_id'          => $this->user->id,
        'category_id'      => $this->category->id,
        'type'             => TransactionType::EXPENSE->value,
        'amount'           => 20000,
        'transaction_date' => '2026-04-10',
    ]);

    expect($forecast->computePaceStatus(Date::parse('2026-04-15')))->toBe(ForecastPaceStatus::UNDER_PACE);
});

it('reports ON_PACE when actual is close to expected', function (): void {
    $forecast = Forecast::factory()->create([
        'user_id'     => $this->user->id,
        'category_id' => $this->category->id,
        'amount'      => 100000,
        'month'       => '2026-04-01',
    ]);

    Transaction::factory()->create([
        'user_id'          => $this->user->id,
        'category_id'      => $this->category->id,
        'type'             => TransactionType::EXPENSE->value,
        'amount'           => 50000,
        'transaction_date' => '2026-04-10',
    ]);

    expect($forecast->computePaceStatus(Date::parse('2026-04-15')))->toBe(ForecastPaceStatus::ON_PACE);
});

it('returns ON_PACE for future months', function (): void {
    $forecast = Forecast::factory()->create([
        'user_id'     => $this->user->id,
        'category_id' => $this->category->id,
        'amount'      => 100000,
        'month'       => '2026-12-01',
    ]);

    expect($forecast->computePaceStatus(Date::parse('2026-04-15')))->toBe(ForecastPaceStatus::ON_PACE);
});

it('clamps pace evaluation to month end for past months', function (): void {
    $forecast = Forecast::factory()->create([
        'user_id'     => $this->user->id,
        'category_id' => $this->category->id,
        'amount'      => 100000,
        'month'       => '2026-01-01',
    ]);

    Transaction::factory()->create([
        'user_id'          => $this->user->id,
        'category_id'      => $this->category->id,
        'type'             => TransactionType::EXPENSE->value,
        'amount'           => 130000,
        'transaction_date' => '2026-01-25',
    ]);

    expect($forecast->computePaceStatus(Date::parse('2026-04-15')))->toBe(ForecastPaceStatus::OVER_PACE);
});

it('computes remaining as amount minus spent to date', function (): void {
    $forecast = Forecast::factory()->create([
        'user_id'     => $this->user->id,
        'category_id' => $this->category->id,
        'amount'      => 100000,
        'month'       => '2026-04-01',
    ]);

    Transaction::factory()->create([
        'user_id'          => $this->user->id,
        'category_id'      => $this->category->id,
        'type'             => TransactionType::EXPENSE->value,
        'amount'           => 35000,
        'transaction_date' => '2026-04-10',
    ]);

    expect($forecast->computeRemaining(Date::parse('2026-04-15')))->toBe(65000);
});
