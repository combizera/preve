<?php

declare(strict_types=1);

use App\Models\Forecast;
use App\Models\ForecastSeries;
use App\Models\User;
use Illuminate\Support\Facades\Date;

beforeEach(function (): void {
    Date::setTestNow(Date::create(2026, 5, 1, 0, 30));
});

afterEach(function (): void {
    Date::setTestNow();
});

it('creates a forecast for the current month for active series', function (): void {
    $user = User::factory()->create();
    $series = ForecastSeries::factory()->create([
        'user_id'        => $user->id,
        'default_amount' => 80000,
        'default_notes'  => 'monthly groceries',
        'is_active'      => true,
    ]);

    $this->artisan('forecasts:rollover')->assertExitCode(0);

    $this->assertDatabaseHas('forecasts', [
        'forecast_series_id' => $series->id,
        'user_id'            => $user->id,
        'category_id'        => $series->category_id,
        'amount'             => 80000,
        'notes'              => 'monthly groceries',
        'month'              => '2026-05-01',
    ]);
});

it('skips paused series', function (): void {
    $user = User::factory()->create();
    $series = ForecastSeries::factory()->paused()->create([
        'user_id' => $user->id,
    ]);

    $this->artisan('forecasts:rollover')->assertExitCode(0);

    $this->assertDatabaseMissing('forecasts', [
        'forecast_series_id' => $series->id,
        'month'              => '2026-05-01',
    ]);
});

it('does not duplicate when current month forecast already exists', function (): void {
    $user = User::factory()->create();
    $series = ForecastSeries::factory()->create([
        'user_id'        => $user->id,
        'default_amount' => 50000,
    ]);

    Forecast::factory()->create([
        'user_id'            => $user->id,
        'category_id'        => $series->category_id,
        'forecast_series_id' => $series->id,
        'amount'             => 99999,
        'month'              => '2026-05-01',
    ]);

    $this->artisan('forecasts:rollover')->assertExitCode(0);

    expect(Forecast::query()->where('forecast_series_id', $series->id)->where('month', '2026-05-01')->count())
        ->toBe(1);

    expect(Forecast::query()->where('forecast_series_id', $series->id)->where('month', '2026-05-01')->first()->amount)
        ->toBe(99999);
});

it('is idempotent when run multiple times', function (): void {
    $user = User::factory()->create();
    ForecastSeries::factory()->create([
        'user_id'        => $user->id,
        'default_amount' => 50000,
    ]);

    $this->artisan('forecasts:rollover')->assertExitCode(0);
    $this->artisan('forecasts:rollover')->assertExitCode(0);

    expect(Forecast::query()->where('month', '2026-05-01')->count())->toBe(1);
});

it('isolates rollover between users', function (): void {
    $userA = User::factory()->create();
    $userB = User::factory()->create();

    $seriesA = ForecastSeries::factory()->create(['user_id' => $userA->id, 'default_amount' => 70000]);
    $seriesB = ForecastSeries::factory()->paused()->create(['user_id' => $userB->id]);

    $this->artisan('forecasts:rollover')->assertExitCode(0);

    $this->assertDatabaseHas('forecasts', [
        'forecast_series_id' => $seriesA->id,
        'month'              => '2026-05-01',
        'amount'             => 70000,
    ]);
    $this->assertDatabaseMissing('forecasts', [
        'forecast_series_id' => $seriesB->id,
        'month'              => '2026-05-01',
    ]);
});
