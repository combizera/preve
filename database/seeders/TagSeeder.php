<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;

final class TagSeeder extends Seeder
{
    public function run(): void
    {
        Tag::factory()
            ->count(5)
            ->recycle([User::all()])
            ->create();
    }
}
