<?php

declare(strict_types=1);

use App\Enums\TransactionType;
use App\Models\Category;
use App\Models\CreditCard;
use App\Models\SavingsBucket;
use App\Models\Transaction;
use App\Models\User;

it('should be able to redirect guests to the login page', function (): void {
    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('login'));
});

it('should be able to visit the dashboard as authenticated user', function (): void {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('dashboard'));
    $response->assertOk();
});

it('separates monthly expenses from monthly investments in savings buckets', function (): void {
    $user = User::factory()->create();
    $category = Category::factory()->create(['user_id' => $user->id]);
    $bucket = SavingsBucket::factory()->for($user)->create();
    $today = now()->startOfMonth()->addDays(2);

    Transaction::factory()->create([
        'user_id'           => $user->id,
        'category_id'       => $category->id,
        'savings_bucket_id' => null,
        'type'              => TransactionType::EXPENSE->value,
        'amount'            => 5000,
        'transaction_date'  => $today,
    ]);

    Transaction::factory()->create([
        'user_id'           => $user->id,
        'category_id'       => $category->id,
        'savings_bucket_id' => $bucket->id,
        'type'              => TransactionType::EXPENSE->value,
        'amount'            => 30000,
        'transaction_date'  => $today,
    ]);

    $this->actingAs($user);

    $this->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('monthlyExpenses', 5000)
            ->where('monthlyInvested', 30000)
        );
});

it('exposes credit cards with the current month invoice total', function (): void {
    $user = User::factory()->create();
    $category = Category::factory()->create([
        'user_id' => $user->id,
        'type'    => TransactionType::EXPENSE->value,
    ]);
    $card = CreditCard::factory()->create(['user_id' => $user->id]);
    $today = now()->startOfMonth()->addDays(2);

    Transaction::factory()->create([
        'user_id'          => $user->id,
        'category_id'      => $category->id,
        'credit_card_id'   => $card->id,
        'type'             => TransactionType::EXPENSE->value,
        'amount'           => 25000,
        'transaction_date' => $today,
    ]);

    $this->actingAs($user);

    $this->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('creditCards', 1)
            ->where('creditCards.0.current_invoice', 25000)
        );
});
