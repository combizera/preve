<?php

namespace Database\Factories;

use App\Enums\CategoryColor;
use App\Enums\CategoryIcon;
use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    protected $model = Category::class;

    public function definition(): array
    {
        $categories = [
            [
                'name' => 'Housing',
                'icon' => CategoryIcon::HOUSE,
                'color' => CategoryColor::BLUE,
            ],
            [
                'name' => 'Groceries',
                'icon' => CategoryIcon::SHOPPING_CART,
                'color' => CategoryColor::GREEN,
            ],
            [
                'name' => 'Transportation',
                'icon' => CategoryIcon::CAR,
                'color' => CategoryColor::ORANGE,
            ],
            [
                'name' => 'Health',
                'icon' => CategoryIcon::HEART,
                'color' => CategoryColor::RED,
            ],
            [
                'name' => 'Coffee',
                'icon' => CategoryIcon::COFFEE,
                'color' => CategoryColor::YELLOW,
            ],
            [
                'name' => 'Food & Dining',
                'icon' => CategoryIcon::UTENSILS,
                'color' => CategoryColor::PURPLE,
            ],
            [
                'name' => 'Travel',
                'icon' => CategoryIcon::PLANE,
                'color' => CategoryColor::BLUE,
            ],
            [
                'name' => 'Work',
                'icon' => CategoryIcon::BRIEFCASE,
                'color' => CategoryColor::GREEN,
            ],
            [
                'name' => 'Entertainment',
                'icon' => CategoryIcon::GAMEPAD_2,
                'color' => CategoryColor::PURPLE,
            ],
            [
                'name' => 'Education',
                'icon' => CategoryIcon::BOOK,
                'color' => CategoryColor::YELLOW,
            ],
        ];

        $category = $this->faker->randomElement($categories);

        return [
            'user_id' => User::factory(),
            'name' => $category['name'],
            'slug' => Str::slug($category['name']),
            'description' => $this->faker->optional()->sentence(),
            'color' => $category['color']->value,
            'icon' => $category['icon']->value,
        ];
    }
}
