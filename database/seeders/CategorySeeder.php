<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Seeder;

final class CategorySeeder extends Seeder
{
    public function run(): void
    {
        Category::factory()
            ->count(15)
            ->recycle([User::all()])
            ->create();
    }
}
