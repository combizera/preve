<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\CreditCardColor;
use App\Models\CreditCard;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CreditCard>
 */
final class CreditCardFactory extends Factory
{
    protected $model = CreditCard::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id'      => User::factory(),
            'name'         => $this->faker->randomElement(['Nubank', 'Inter', 'Itaú', 'Santander']),
            'last_four'    => (string) $this->faker->numberBetween(1000, 9999),
            'closing_day'  => $this->faker->numberBetween(1, 28),
            'due_day'      => $this->faker->numberBetween(1, 28),
            'color'        => $this->faker->randomElement(CreditCardColor::cases())->value,
            'credit_limit' => $this->faker->numberBetween(100000, 5000000),
        ];
    }
}
