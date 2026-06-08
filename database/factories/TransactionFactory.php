<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\TransactionType;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Transaction>
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
            'amount'           => $this->faker->numberBetween(1000, 9999),
            'type'             => $this->faker->randomElement(TransactionType::cases())->value,
            'description'      => $this->faker->sentence(),
            'notes'            => $this->faker->optional()->paragraph(),
            'transaction_date' => $this->faker->dateTimeBetween('-1 year', 'now'),
        ];
    }

    /**
     * Attach the given tag IDs after the transaction is created.
     *
     * @param  array<int>  $tagIds
     */
    public function withTags(array $tagIds): self
    {
        return $this->afterCreating(function (Transaction $transaction) use ($tagIds): void {
            $transaction->tags()->sync($tagIds);
        });
    }
}
