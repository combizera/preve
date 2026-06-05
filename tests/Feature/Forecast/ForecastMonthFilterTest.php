<?php

declare(strict_types=1);

use App\Enums\TransactionType;
use App\Models\Category;
use App\Models\Forecast;
use App\Models\ForecastSeries;
use App\Models\User;
use Illuminate\Support\Facades\Date;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function (): void {
    Date::setTestNow(Date::create(2026, 6, 15));
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

afterEach(function (): void {
    Date::setTestNow();
});

it('defaults to the current month when no month param is given', function (): void {
    ForecastSeries::factory()->create([
        'user_id'        => $this->user->id,
        'default_amount' => 70000,
        'is_active'      => true,
    ]);

    $this->get(route('forecasts.index'))
        ->assertSuccessful()
        ->assertInertia(
            fn (Assert $page): Assert => $page
                ->component('Forecast')
                ->where('month', '2026-06')
                ->where('hasForecasts', true)
                ->has('forecasts', 1)
                ->where('forecasts.0.amount', 70000)
        );
});

it('filters forecasts to the requested month', function (): void {
    $category = Category::factory()->create([
        'user_id' => $this->user->id,
        'type'    => TransactionType::EXPENSE->value,
    ]);

    $series = ForecastSeries::factory()->paused()->create([
        'user_id'     => $this->user->id,
        'category_id' => $category->id,
    ]);

    Forecast::factory()->create([
        'user_id'            => $this->user->id,
        'category_id'        => $category->id,
        'forecast_series_id' => $series->id,
        'month'              => '2026-04-01',
        'amount'             => 40000,
    ]);

    Forecast::factory()->create([
        'user_id'            => $this->user->id,
        'category_id'        => $category->id,
        'forecast_series_id' => $series->id,
        'month'              => '2026-05-01',
        'amount'             => 50000,
    ]);

    $this->get(route('forecasts.index', ['month' => '2026-04']))
        ->assertSuccessful()
        ->assertInertia(
            fn (Assert $page): Assert => $page
                ->where('month', '2026-04')
                ->has('forecasts', 1)
                ->where('forecasts.0.amount', 40000)
        );
});

it('reports hasForecasts false and an empty month when the user has no series', function (): void {
    $this->get(route('forecasts.index'))
        ->assertSuccessful()
        ->assertInertia(
            fn (Assert $page): Assert => $page
                ->where('hasForecasts', false)
                ->has('forecasts', 0)
        );
});

it('rejects a malformed month param', function (): void {
    $this->get(route('forecasts.index', ['month' => 'not-a-month']))
        ->assertInvalid(['month']);
});
