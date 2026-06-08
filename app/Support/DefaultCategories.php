<?php

declare(strict_types=1);

namespace App\Support;

use App\Enums\AccentColor;
use App\Enums\CategoryIcon;
use App\Enums\TransactionType;

final class DefaultCategories
{
    public static function get(): array
    {
        return [
            // ======================
            // EXPENSE CATEGORIES (5)
            // ======================
            [
                'name'  => 'Housing',
                'type'  => TransactionType::EXPENSE,
                'icon'  => CategoryIcon::HOUSE,
                'color' => AccentColor::BLUE,
            ],
            [
                'name'  => 'Groceries',
                'type'  => TransactionType::EXPENSE,
                'icon'  => CategoryIcon::SHOPPING_CART,
                'color' => AccentColor::GREEN,
            ],
            [
                'name'  => 'Transportation',
                'type'  => TransactionType::EXPENSE,
                'icon'  => CategoryIcon::CAR,
                'color' => AccentColor::ORANGE,
            ],
            [
                'name'  => 'Food & Dining',
                'type'  => TransactionType::EXPENSE,
                'icon'  => CategoryIcon::UTENSILS,
                'color' => AccentColor::PURPLE,
            ],
            [
                'name'  => 'Entertainment',
                'type'  => TransactionType::EXPENSE,
                'icon'  => CategoryIcon::GAMEPAD_2,
                'color' => AccentColor::YELLOW,
            ],

            // ======================
            // INCOME CATEGORIES (3)
            // ======================
            [
                'name'  => 'Salary',
                'type'  => TransactionType::INCOME,
                'icon'  => CategoryIcon::BRIEFCASE,
                'color' => AccentColor::GREEN,
            ],
            [
                'name'  => 'Freelance',
                'type'  => TransactionType::INCOME,
                'icon'  => CategoryIcon::LAPTOP,
                'color' => AccentColor::BLUE,
            ],
            [
                'name'  => 'Investments',
                'type'  => TransactionType::INCOME,
                'icon'  => CategoryIcon::SHOPPING_BAG,
                'color' => AccentColor::PURPLE,
            ],
        ];
    }
}
