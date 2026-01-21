<?php

namespace Database\Factories;

use App\Enums\CategoryColor;
use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->word();

        return [
            'user_id' => User::factory(),
            'name' => $name,
            'slug' => \Str::slug($name),
            'description' => $this->faker->optional()->sentence(),
            'color' => $this->faker->randomElement(CategoryColor::cases())->value,
            'icon' => 'home',
        ];
    }
}
