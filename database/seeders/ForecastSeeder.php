<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\TransactionType;
use App\Models\Forecast;
use App\Models\User;
use Illuminate\Database\Seeder;

final class ForecastSeeder extends Seeder
{
    public function run(): void
    {
        foreach (User::all() as $user) {
            $category = $user->categories()
                ->where('type', TransactionType::EXPENSE)
                ->availableForForecast()
                ->first();

            if ($category === null) {
                continue;
            }

            Forecast::factory()->for($user)->for($category)->create([
                'month' => now()->startOfMonth()->toDateString(),
            ]);
        }
    }
}
