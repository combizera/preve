<?php

declare(strict_types=1);

use App\Enums\TransactionType;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;

beforeEach(function () {
    $user = User::factory()->create();
    $this->actingAs($user);
});

// CREATE
it('should be able to create transaction', function () {
    $category = Category::factory()->create([
        'user_id' => auth()->id(),
        'type'    => TransactionType::EXPENSE->value,
    ]);

    $response = $this->post(route('transactions.store'), [
        'category_id'      => $category->id,
        'tag_id'           => null,
        'amount'           => 150.00,
        'type'             => TransactionType::EXPENSE->value,
        'description'      => 'Grocery shopping',
        'notes'            => null,
        'transaction_date' => '2026-01-15',
    ]);

    $response->assertRedirect(route('transactions.index'));

    $this->assertDatabaseHas('transactions', [
        'category_id' => $category->id,
        'amount'      => 150.00,
        'type'        => TransactionType::EXPENSE->value,
        'description' => 'Grocery shopping',
    ]);
});

// READ
it('should be able to view transactions index', function () {
    $response = $this->get(route('transactions.index'));

    $response->assertStatus(200);
});

// EDIT
it('should be able to edit transaction', function () {
    $category = Category::factory()->create([
        'user_id' => auth()->id(),
        'type'    => TransactionType::INCOME->value,
    ]);

    $transaction = Transaction::factory()->create([
        'user_id'     => auth()->id(),
        'category_id' => $category->id,
        'amount'      => 100.00,
        'type'        => TransactionType::INCOME->value,
        'description' => 'Salary',
    ]);

    $response = $this->put(route('transactions.update', $transaction->id), [
        'category_id'      => $category->id,
        'amount'           => 200.00,
        'type'             => TransactionType::INCOME->value,
        'description'      => 'Updated Salary',
        'transaction_date' => '2026-01-20',
    ]);

    $response->assertRedirect(route('transactions.index'));

    $this->assertDatabaseHas('transactions', [
        'id'          => $transaction->id,
        'amount'      => 200.00,
        'description' => 'Updated Salary',
    ]);
});

it('should not be able to edit transaction that you do not own', function () {
    $transaction = Transaction::factory()->create();

    $category = Category::factory()->create([
        'user_id' => auth()->id(),
        'type'    => TransactionType::INCOME->value,
    ]);

    $response = $this->put(route('transactions.update', $transaction->id), [
        'category_id'      => $category->id,
        'description'      => 'Updated Transaction',
        'type'             => TransactionType::INCOME->value,
        'amount'           => 200.00,
        'transaction_date' => '2026-01-20',
    ]);

    $response->assertStatus(403);
});

// DELETE
it('should be able to delete transaction', function () {
    $transaction = Transaction::factory()->create([
        'user_id' => auth()->id(),
    ]);

    $response = $this->delete(route('transactions.destroy', $transaction->id));

    $response->assertRedirect(route('transactions.index'));
});

it('should not be able to delete transaction that you do not own', function () {
    $transaction = Transaction::factory()->create();

    $response = $this->delete(route('transactions.destroy', $transaction->id));

    $response->assertStatus(403);
});
