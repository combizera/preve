<?php

declare(strict_types=1);

use App\Models\Forecast;
use App\Models\ForecastSeries;
use App\Models\User;

beforeEach(function (): void {
    $user = User::factory()->create();
    $this->actingAs($user);
});

it('pauses an active forecast series', function (): void {
    $series = ForecastSeries::factory()->create([
        'user_id'   => auth()->id(),
        'is_active' => true,
    ]);

    $forecast = Forecast::factory()->create([
        'user_id'            => auth()->id(),
        'category_id'        => $series->category_id,
        'forecast_series_id' => $series->id,
    ]);

    $response = $this->patch(route('forecasts.toggle', $forecast->id));

    $response->assertRedirect(route('forecasts.index'));

    expect($series->fresh()->is_active)->toBeFalse();
});

it('resumes a paused forecast series', function (): void {
    $series = ForecastSeries::factory()->paused()->create([
        'user_id' => auth()->id(),
    ]);

    $forecast = Forecast::factory()->create([
        'user_id'            => auth()->id(),
        'category_id'        => $series->category_id,
        'forecast_series_id' => $series->id,
    ]);

    $response = $this->patch(route('forecasts.toggle', $forecast->id));

    $response->assertRedirect(route('forecasts.index'));

    expect($series->fresh()->is_active)->toBeTrue();
});

it('does not allow toggling another users forecast', function (): void {
    $forecast = Forecast::factory()->create();

    $response = $this->patch(route('forecasts.toggle', $forecast->id));

    $response->assertForbidden();
});
