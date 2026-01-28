<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\FrequencyType;
use App\Enums\TransactionType;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\RecurringTransaction>
 */
final class RecurringTransactionFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id'      => User::factory(),
            'category_id'  => Category::factory(),
            'tag_id'       => Tag::factory(),
            'amount'       => $this->faker->randomFloat(2, 10, 200),
            'frequency'    => $this->faker->randomElement(FrequencyType::cases())->value,
            'type'         => $this->faker->randomElement(TransactionType::cases())->value,
            'description'  => $this->faker->sentence(),
            'day_of_month' => $this->faker->numberBetween(1, 28),
            'start_date'   => $this->faker->dateTimeBetween('-1 year', 'now')->format('Y-m-d'),
            'end_date'     => $this->faker->optional()->dateTimeBetween('now', '+1 year')->format('Y-m-d'),
        ];
    }
}
