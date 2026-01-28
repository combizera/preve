<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Tag;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Seeder;

final class TransactionSeeder extends Seeder
{
    public function run(): void
    {
        Transaction::factory()
            ->count(100)
            ->recycle([User::all(), Category::all(), Tag::all()])
            ->create();
    }
}
