<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

final class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::factory()->create([
            'name'     => 'Test User',
            'email'    => 'admin@site.com',
            'password' => 'admin@site.com',
        ]);

        User::factory()->count(5)->create();
    }
}
