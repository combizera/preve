<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;

final class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            CreditCardSeeder::class,
            TransactionSeeder::class,
            RecurringTransactionSeeder::class,
            ForecastSeeder::class,
        ]);
    }
}
