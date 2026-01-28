<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\RecurringTransaction;
use App\Models\User;
use Database\Factories\RecurringTransactionFactory;
use Illuminate\Database\Seeder;

final class RecurringTransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();

        foreach ($users as $user) {
            $categories = $user->categories;

            if ($categories->isEmpty()) {
                continue;
            }

            foreach (RecurringTransactionFactory::commonRecurringTransactions() as $transaction) {
                $categoryType = $categories->where('type', $transaction['type']);

                if ($categoryType->isEmpty()) {
                    continue;
                }

                $category = $categoryType->random();

                RecurringTransaction::factory()->create([
                    'user_id'     => $user->id,
                    'category_id' => $category->id,
                    'tag_id'      => null,
                    'start_date'  => now()->subMonths(3)->format('Y-m-d'),
                    'end_date'    => null,
                    ...$transaction,
                ]);
            }
        }
    }
}
