<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\CreditCard;
use App\Models\User;
use Illuminate\Database\Seeder;

final class CreditCardSeeder extends Seeder
{
    public function run(): void
    {
        User::all()->each(function (User $user): void {
            CreditCard::factory()->count(2)->create([
                'user_id' => $user->id,
            ]);
        });
    }
}
