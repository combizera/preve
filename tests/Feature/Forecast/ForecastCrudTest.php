<?php

declare(strict_types=1);

use App\Enums\TransactionType;
use App\Models\Category;
use App\Models\Forecast;
use App\Models\ForecastSeries;
use App\Models\User;

beforeEach(function (): void {
    $user = User::factory()->create();
    $this->actingAs($user);
});

it('creates a forecast series together with the first instance on store', function (): void {
    $category = Category::factory()->create([
        'user_id' => auth()->id(),
        'type'    => TransactionType::EXPENSE->value,
    ]);

    $response = $this->post(route('forecasts.store'), [
        'category_id' => $category->id,
        'amount'      => 100000,
        'month'       => '2026-04-15',
        'notes'       => 'monthly groceries cap',
    ]);

    $response->assertRedirect(route('forecasts.index'));

    $this->assertDatabaseHas('forecast_series', [
        'user_id'        => auth()->id(),
        'category_id'    => $category->id,
        'default_amount' => 100000,
        'default_notes'  => 'monthly groceries cap',
        'is_active'      => true,
    ]);

    $this->assertDatabaseHas('forecasts', [
        'user_id'     => auth()->id(),
        'category_id' => $category->id,
        'amount'      => 100000,
        'month'       => '2026-04-01',
    ]);
});

it('rejects creating a forecast for a category that already has a series', function (bool $paused): void {
    $category = Category::factory()->create([
        'user_id' => auth()->id(),
        'type'    => TransactionType::EXPENSE->value,
    ]);

    $seriesFactory = ForecastSeries::factory();
    if ($paused) {
        $seriesFactory = $seriesFactory->paused();
    }

    $seriesFactory->create([
        'user_id'     => auth()->id(),
        'category_id' => $category->id,
    ]);

    $response = $this->post(route('forecasts.store'), [
        'category_id' => $category->id,
        'amount'      => 200000,
        'month'       => '2026-05-01',
    ]);

    $response->assertSessionHasErrors('category_id');
    $this->assertDatabaseCount('forecast_series', 1);
})->with([
    'active series' => false,
    'paused series' => true,
]);

it('rejects forecast for income category', function (): void {
    $category = Category::factory()->create([
        'user_id' => auth()->id(),
        'type'    => TransactionType::INCOME->value,
    ]);

    $response = $this->post(route('forecasts.store'), [
        'category_id' => $category->id,
        'amount'      => 100000,
        'month'       => '2026-04-01',
    ]);

    $response->assertSessionHasErrors('category_id');
    $this->assertDatabaseCount('forecasts', 0);
});

it('rejects forecast referencing another users category', function (): void {
    $otherUserCategory = Category::factory()->create([
        'type' => TransactionType::EXPENSE->value,
    ]);

    $response = $this->post(route('forecasts.store'), [
        'category_id' => $otherUserCategory->id,
        'amount'      => 100000,
        'month'       => '2026-04-01',
    ]);

    $response->assertSessionHasErrors('category_id');
});

it('rejects zero or negative amount', function (int $amount): void {
    $category = Category::factory()->create([
        'user_id' => auth()->id(),
        'type'    => TransactionType::EXPENSE->value,
    ]);

    $response = $this->post(route('forecasts.store'), [
        'category_id' => $category->id,
        'amount'      => $amount,
        'month'       => '2026-04-01',
    ]);

    $response->assertSessionHasErrors('amount');
})->with([
    'zero'     => 0,
    'negative' => -100,
]);

it('renders forecasts index successfully', function (): void {
    $response = $this->get(route('forecasts.index'));

    $response->assertSuccessful();
});

it('updates only the instance amount when apply_to_default is unset', function (): void {
    $category = Category::factory()->create([
        'user_id' => auth()->id(),
        'type'    => TransactionType::EXPENSE->value,
    ]);

    $series = ForecastSeries::factory()->create([
        'user_id'        => auth()->id(),
        'category_id'    => $category->id,
        'default_amount' => 100000,
    ]);

    $forecast = Forecast::factory()->create([
        'user_id'            => auth()->id(),
        'category_id'        => $category->id,
        'forecast_series_id' => $series->id,
        'amount'             => 100000,
    ]);

    $response = $this->put(route('forecasts.update', $forecast->id), [
        'amount' => 200000,
    ]);

    $response->assertRedirect(route('forecasts.index'));

    expect($forecast->fresh()->amount)->toBe(200000);
    expect($series->fresh()->default_amount)->toBe(100000);
});

it('updates instance and series default when apply_to_default is true', function (): void {
    $category = Category::factory()->create([
        'user_id' => auth()->id(),
        'type'    => TransactionType::EXPENSE->value,
    ]);

    $series = ForecastSeries::factory()->create([
        'user_id'        => auth()->id(),
        'category_id'    => $category->id,
        'default_amount' => 100000,
    ]);

    $forecast = Forecast::factory()->create([
        'user_id'            => auth()->id(),
        'category_id'        => $category->id,
        'forecast_series_id' => $series->id,
        'amount'             => 100000,
    ]);

    $response = $this->put(route('forecasts.update', $forecast->id), [
        'amount'           => 200000,
        'apply_to_default' => true,
    ]);

    $response->assertRedirect(route('forecasts.index'));

    expect($forecast->fresh()->amount)->toBe(200000);
    expect($series->fresh()->default_amount)->toBe(200000);
});

it('does not allow updating another users forecast', function (): void {
    $forecast = Forecast::factory()->create();

    $response = $this->put(route('forecasts.update', $forecast->id), [
        'amount' => 130000,
    ]);

    $response->assertForbidden();
});

it('deletes only the current instance, leaving the series intact', function (): void {
    $series = ForecastSeries::factory()->create([
        'user_id' => auth()->id(),
    ]);

    $forecast = Forecast::factory()->create([
        'user_id'            => auth()->id(),
        'category_id'        => $series->category_id,
        'forecast_series_id' => $series->id,
    ]);

    $response = $this->delete(route('forecasts.destroy', $forecast->id));

    $response->assertRedirect(route('forecasts.index'));
    $this->assertDatabaseMissing('forecasts', ['id' => $forecast->id]);
    $this->assertDatabaseHas('forecast_series', ['id' => $series->id]);
});

it('deletes the entire series and all its instances via destroySeries', function (): void {
    $series = ForecastSeries::factory()->create([
        'user_id' => auth()->id(),
    ]);

    $forecast = Forecast::factory()->create([
        'user_id'            => auth()->id(),
        'category_id'        => $series->category_id,
        'forecast_series_id' => $series->id,
    ]);

    $response = $this->delete(route('forecast-series.destroy', $series->id));

    $response->assertRedirect();
    $this->assertDatabaseMissing('forecasts', ['id' => $forecast->id]);
    $this->assertDatabaseMissing('forecast_series', ['id' => $series->id]);
});

it('does not allow deleting another users forecast', function (): void {
    $forecast = Forecast::factory()->create();

    $response = $this->delete(route('forecasts.destroy', $forecast->id));

    $response->assertForbidden();
});
