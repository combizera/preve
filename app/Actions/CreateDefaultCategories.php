<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\User;
use App\Support\DefaultCategories;

final class CreateDefaultCategories
{
    public function execute(User $user): void
    {
        $defaultCategories = DefaultCategories::get();

        foreach ($defaultCategories as $category) {
            $user->categories()->create([
                'name'  => $category['name'],
                'slug'  => str()->slug($category['name']),
                'type'  => $category['type']->value,
                'icon'  => $category['icon']->value,
                'color' => $category['color']->value,
            ]);
        }
    }
}
