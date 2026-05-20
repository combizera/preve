<?php

declare(strict_types=1);

use App\Enums\TransactionType;
use App\Models\Category;
use App\Models\Forecast;
use App\Models\ForecastSeries;
use App\Models\Transaction;
use App\Models\User;
use App\Services\ForecastService;
use Illuminate\Support\Facades\Date;

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->category = Category::factory()->create([
        'user_id' => $this->user->id,
        'type'    => TransactionType::EXPENSE->value,
    ]);
    $this->service = new ForecastService();
});

it('does not double-count pending transactions in budgeted categories for the current month', function (): void {
    Forecast::factory()->create([
        'user_id'     => $this->user->id,
        'category_id' => $this->category->id,
        'amount'      => 20000,
        'month'       => '2026-04-01',
    ]);

    Transaction::factory()->create([
        'user_id'          => $this->user->id,
        'category_id'      => $this->category->id,
        'type'             => TransactionType::EXPENSE->value,
        'amount'           => 5000,
        'transaction_date' => '2026-04-05',
    ]);

    Transaction::factory()->create([
        'user_id'          => $this->user->id,
        'category_id'      => $this->category->id,
        'type'             => TransactionType::EXPENSE->value,
        'amount'           => 8000,
        'transaction_date' => '2026-04-25',
    ]);

    $availableBalance = 100000 - 5000;
    $now = Date::parse('2026-04-15');

    $forecast = $this->service->calculate($this->user, $now, $availableBalance, 4, 2026);

    expect($forecast)->toBe(80000);
});

it('clamps shortfall at zero when paid spend already exceeds the budget for the current month', function (): void {
    Forecast::factory()->create([
        'user_id'     => $this->user->id,
        'category_id' => $this->category->id,
        'amount'      => 10000,
        'month'       => '2026-04-01',
    ]);

    Transaction::factory()->create([
        'user_id'          => $this->user->id,
        'category_id'      => $this->category->id,
        'type'             => TransactionType::EXPENSE->value,
        'amount'           => 15000,
        'transaction_date' => '2026-04-05',
    ]);

    $availableBalance = 100000 - 15000;
    $now = Date::parse('2026-04-15');

    $forecast = $this->service->calculate($this->user, $now, $availableBalance, 4, 2026);

    expect($forecast)->toBe(85000);
});

it('does not double-count scheduled transactions in budgeted categories for a future month', function (): void {
    Forecast::factory()->create([
        'user_id'     => $this->user->id,
        'category_id' => $this->category->id,
        'amount'      => 30000,
        'month'       => '2026-05-01',
    ]);

    Transaction::factory()->create([
        'user_id'          => $this->user->id,
        'category_id'      => $this->category->id,
        'type'             => TransactionType::EXPENSE->value,
        'amount'           => 12000,
        'transaction_date' => '2026-05-10',
    ]);

    $availableBalance = 100000;
    $now = Date::parse('2026-04-15');

    $forecast = $this->service->calculate($this->user, $now, $availableBalance, 5, 2026);

    expect($forecast)->toBe(70000);
});

it('ignores forecasts whose series is paused', function (): void {
    $series = ForecastSeries::factory()->paused()->create([
        'user_id'     => $this->user->id,
        'category_id' => $this->category->id,
    ]);
    Forecast::factory()->create([
        'user_id'            => $this->user->id,
        'category_id'        => $this->category->id,
        'forecast_series_id' => $series->id,
        'amount'             => 50000,
        'month'              => '2026-04-01',
    ]);

    $availableBalance = 100000;
    $now = Date::parse('2026-04-15');

    $forecast = $this->service->calculate($this->user, $now, $availableBalance, 4, 2026);

    expect($forecast)->toBe(100000);
});

it('returns the historical month balance for past months without applying any shortfall', function (): void {
    Forecast::factory()->create([
        'user_id'     => $this->user->id,
        'category_id' => $this->category->id,
        'amount'      => 50000,
        'month'       => '2026-01-01',
    ]);

    Transaction::factory()->create([
        'user_id'          => $this->user->id,
        'category_id'      => $this->category->id,
        'type'             => TransactionType::EXPENSE->value,
        'amount'           => 20000,
        'transaction_date' => '2026-01-10',
    ]);
    Transaction::factory()->create([
        'user_id'          => $this->user->id,
        'category_id'      => $this->category->id,
        'type'             => TransactionType::INCOME->value,
        'amount'           => 60000,
        'transaction_date' => '2026-01-20',
    ]);

    $availableBalance = 999999;
    $now = Date::parse('2026-04-15');

    $forecast = $this->service->calculate($this->user, $now, $availableBalance, 1, 2026);

    expect($forecast)->toBe(40000);
});

it('dailyForecastedSpend uses unrecorded portion (no double-count of pending)', function (): void {
    Forecast::factory()->create([
        'user_id'     => $this->user->id,
        'category_id' => $this->category->id,
        'amount'      => 30000,
        'month'       => '2026-04-01',
    ]);

    Transaction::factory()->create([
        'user_id'          => $this->user->id,
        'category_id'      => $this->category->id,
        'type'             => TransactionType::EXPENSE->value,
        'amount'           => 10000,
        'transaction_date' => '2026-04-25',
    ]);

    $now = Date::parse('2026-04-15');
    $chartDate = Date::parse('2026-04-01');

    $daily = $this->service->dailyForecastedSpend($this->user, $chartDate, $now);

    expect($daily)->toBe(intdiv(20000, 15));
});
