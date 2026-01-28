<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\CategoryColor;
use App\Enums\CategoryIcon;
use App\Enums\TransactionType;
use App\Models\Category;
use App\Models\User;
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
        $categories = [
            // ======================
            // EXPENSE CATEGORIES (5)
            // ======================
            [
                'name'  => 'Housing',
                'type'  => TransactionType::EXPENSE,
                'icon'  => CategoryIcon::HOUSE,
                'color' => CategoryColor::BLUE,
            ],
            [
                'name'  => 'Groceries',
                'type'  => TransactionType::EXPENSE,
                'icon'  => CategoryIcon::SHOPPING_CART,
                'color' => CategoryColor::GREEN,
            ],
            [
                'name'  => 'Transportation',
                'type'  => TransactionType::EXPENSE,
                'icon'  => CategoryIcon::CAR,
                'color' => CategoryColor::ORANGE,
            ],
            [
                'name'  => 'Food & Dining',
                'type'  => TransactionType::EXPENSE,
                'icon'  => CategoryIcon::UTENSILS,
                'color' => CategoryColor::PURPLE,
            ],
            [
                'name'  => 'Entertainment',
                'type'  => TransactionType::EXPENSE,
                'icon'  => CategoryIcon::GAMEPAD_2,
                'color' => CategoryColor::YELLOW,
            ],

            // ======================
            // INCOME CATEGORIES (3)
            // ======================
            [
                'name'  => 'Salary',
                'type'  => TransactionType::INCOME,
                'icon'  => CategoryIcon::BRIEFCASE,
                'color' => CategoryColor::GREEN,
            ],
            [
                'name'  => 'Freelance',
                'type'  => TransactionType::INCOME,
                'icon'  => CategoryIcon::LAPTOP,
                'color' => CategoryColor::BLUE,
            ],
            [
                'name'  => 'Investments',
                'type'  => TransactionType::INCOME,
                'icon'  => CategoryIcon::SHOPPING_BAG,
                'color' => CategoryColor::PURPLE,
            ],
        ];

        $category = $this->faker->randomElement($categories);

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
