<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\FrequencyType;
use App\Enums\TransactionType;
use App\Models\Category;
use App\Models\RecurringTransaction;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RecurringTransaction>
 */
final class RecurringTransactionFactory extends Factory
{
    /**
     * Get a list of common recurring transactions
     *
     * @return array<int, array<string, mixed>>
     */
    public static function commonRecurringTransactions(): array
    {
        return [
            // Income
            [
                'description'  => 'Monthly Salary',
                'amount'       => 500000,
                'type'         => TransactionType::INCOME->value,
                'frequency'    => FrequencyType::MONTHLY->value,
                'day_of_month' => 5,
            ],
            [
                'description'  => 'Freelance Work',
                'amount'       => 20000,
                'type'         => TransactionType::INCOME->value,
                'frequency'    => FrequencyType::MONTHLY->value,
                'day_of_month' => 15,
            ],
            [
                'description'  => 'Rental Income',
                'amount'       => 150000,
                'type'         => TransactionType::INCOME->value,
                'frequency'    => FrequencyType::MONTHLY->value,
                'day_of_month' => 10,
            ],

            // Expenses
            [
                'description'  => 'Rent Payment',
                'amount'       => 180000,
                'type'         => TransactionType::EXPENSE->value,
                'frequency'    => FrequencyType::MONTHLY->value,
                'day_of_month' => 10,
            ],
            [
                'description'  => 'Condo Fee',
                'amount'       => 35000,
                'type'         => TransactionType::EXPENSE->value,
                'frequency'    => FrequencyType::MONTHLY->value,
                'day_of_month' => 15,
            ],
            [
                'description'  => 'Internet Service',
                'amount'       => 9990,
                'type'         => TransactionType::EXPENSE->value,
                'frequency'    => FrequencyType::MONTHLY->value,
                'day_of_month' => 20,
            ],
            [
                'description'  => 'Electric Bill',
                'amount'       => 18000,
                'type'         => TransactionType::EXPENSE->value,
                'frequency'    => FrequencyType::MONTHLY->value,
                'day_of_month' => 12,
            ],
            [
                'description'  => 'Mobile Phone Plan',
                'amount'       => 7990,
                'type'         => TransactionType::EXPENSE->value,
                'frequency'    => FrequencyType::MONTHLY->value,
                'day_of_month' => 8,
            ],
            [
                'description'  => 'Gym Membership',
                'amount'       => 12000,
                'type'         => TransactionType::EXPENSE->value,
                'frequency'    => FrequencyType::MONTHLY->value,
                'day_of_month' => 1,
            ],
            [
                'description'  => 'Streaming Subscription',
                'amount'       => 5590,
                'type'         => TransactionType::EXPENSE->value,
                'frequency'    => FrequencyType::MONTHLY->value,
                'day_of_month' => 25,
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id'      => User::factory(),
            'category_id'  => Category::factory(),
            'tag_id'       => Tag::factory(),
            'amount'       => $this->faker->numberBetween(1000, 999999),
            'frequency'    => $this->faker->randomElement(FrequencyType::cases())->value,
            'type'         => $this->faker->randomElement(TransactionType::cases())->value,
            'description'  => $this->faker->sentence(),
            'is_active'    => $this->faker->boolean(80),
            'day_of_month' => $this->faker->numberBetween(1, 28),
            'start_date'   => $this->faker->dateTimeBetween('-1 year', 'now')->format('Y-m-d'),
            'end_date'     => $this->faker->optional()->dateTimeBetween('now', '+1 year'),
        ];
    }
}
