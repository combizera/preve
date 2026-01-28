<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Tag>
 */
final class TagFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->word();

        return [
            'user_id'     => User::factory(),
            'name'        => $name,
            'slug'        => Str::slug($name),
            'description' => $this->faker->optional()->sentence(),
        ];
    }
}
