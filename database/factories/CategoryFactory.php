<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Category;
use App\Models\User;
use App\Support\DefaultCategories;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Category>
 */
final class CategoryFactory extends Factory
{
    protected $model = Category::class;

    public function definition(): array
    {
        $category = $this->faker->randomElement(DefaultCategories::get());

        return [
            'user_id'     => User::factory(),
            'name'        => $category['name'],
            'slug'        => Str::slug($category['name']),
            'description' => $this->faker->optional()->sentence(),
            'type'        => $category['type']->value,
            'color'       => $category['color']->value,
            'icon'        => $category['icon']->value,
        ];
    }
}
