<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\AccentColor;
use App\Enums\SavingsBucketIcon;
use App\Models\SavingsBucket;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SavingsBucket>
 */
final class SavingsBucketFactory extends Factory
{
    protected $model = SavingsBucket::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id'        => User::factory(),
            'name'           => $this->faker->randomElement(['Emergency Fund', 'Car', 'Trave', 'Laptop']),
            'target_amount'  => $this->faker->numberBetween(100000, 5000000),
            'current_amount' => 0,
            'color'          => $this->faker->randomElement(AccentColor::cases())->value,
            'icon'           => SavingsBucketIcon::PIGGY_BANK->value,
        ];
    }
}
