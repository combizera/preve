<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Category;
use App\Models\ForecastSeries;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ForecastSeries>
 */
final class ForecastSeriesFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id'        => User::factory(),
            'category_id'    => Category::factory(),
            'default_amount' => $this->faker->numberBetween(10000, 500000),
            'default_notes'  => $this->faker->optional()->sentence(),
            'is_active'      => true,
        ];
    }

    public function paused(): self
    {
        return $this->state(fn (): array => ['is_active' => false]);
    }
}
