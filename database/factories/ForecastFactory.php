<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Category;
use App\Models\Forecast;
use App\Models\ForecastSeries;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Date;

/**
 * @extends Factory<Forecast>
 */
final class ForecastFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id'            => User::factory(),
            'category_id'        => Category::factory(),
            'forecast_series_id' => fn (array $attributes): string => ForecastSeries::factory()->create([
                'user_id'     => $attributes['user_id'],
                'category_id' => $attributes['category_id'],
            ])->id,
            'amount' => $this->faker->numberBetween(10000, 500000),
            'month'  => Date::today()->startOfMonth()->toDateString(),
            'notes'  => $this->faker->optional()->sentence(),
        ];
    }
}
