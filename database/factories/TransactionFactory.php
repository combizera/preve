<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\TransactionType;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Category>
 */
final class TransactionFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id'               => $this->faker->uuid(),
            'user_id'          => User::factory(),
            'category_id'      => Category::factory(),
            'tag_id'           => Tag::factory(),
            'amount'           => $this->faker->randomFloat(2, 10, 200),
            'type'             => $this->faker->randomElement(TransactionType::cases())->value,
            'description'      => $this->faker->sentence(),
            'notes'            => $this->faker->optional()->paragraph(),
            'transaction_date' => $this->faker->dateTimeBetween('-1 year', 'now'),
        ];
    }
}
