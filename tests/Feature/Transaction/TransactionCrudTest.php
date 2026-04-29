<?php

declare(strict_types=1);

use App\Enums\TransactionType;
use App\Models\Category;
use App\Models\Tag;
use App\Models\Transaction;
use App\Models\User;

beforeEach(function (): void {
    $user = User::factory()->create();
    $this->actingAs($user);
});

// CREATE
it('should be able to create transaction', function (): void {
    $category = Category::factory()->create([
        'user_id' => auth()->id(),
        'type'    => TransactionType::EXPENSE->value,
    ]);

    $response = $this->post(route('transactions.store'), [
        'category_id'      => $category->id,
        'amount'           => 150.00,
        'type'             => TransactionType::EXPENSE->value,
        'description'      => 'Grocery shopping',
        'notes'            => null,
        'transaction_date' => '2026-01-15',
    ]);

    $response->assertRedirectBack();

    $this->assertDatabaseHas('transactions', [
        'category_id' => $category->id,
        'amount'      => 150.00,
        'type'        => TransactionType::EXPENSE->value,
        'description' => 'Grocery shopping',
    ]);
});

it('should not be able to create transaction with type different from category', function (): void {
    $category = Category::factory()->create([
        'user_id' => auth()->id(),
        'type'    => TransactionType::INCOME->value,
    ]);

    $response = $this->post(route('transactions.store'), [
        'category_id'      => $category->id,
        'amount'           => 150.00,
        'type'             => TransactionType::EXPENSE->value,
        'description'      => 'Grocery shopping',
        'notes'            => null,
        'transaction_date' => '2026-01-15',
    ]);

    $response->assertSessionHasErrors('category_id');

    $this->assertDatabaseMissing('transactions', [
        'category_id' => $category->id,
        'description' => 'Grocery shopping',
    ]);
});

it('should not be able to create transaction with zero or negative amount', function (int|float $amount): void {
    $category = Category::factory()->create([
        'user_id' => auth()->id(),
        'type'    => TransactionType::EXPENSE->value,
    ]);

    $response = $this->post(route('transactions.store'), [
        'category_id'      => $category->id,
        'amount'           => $amount,
        'type'             => TransactionType::EXPENSE->value,
        'description'      => 'Grocery shopping',
        'notes'            => null,
        'transaction_date' => '2026-01-15',
    ]);

    $response->assertSessionHasErrors('amount');

    $this->assertDatabaseMissing('transactions', [
        'category_id' => $category->id,
        'description' => 'Grocery shopping',
    ]);
})->with([
    'zero'     => 0,
    'negative' => -100,
]);

it('should attach multiple tags when creating a transaction', function (): void {
    $category = Category::factory()->create([
        'user_id' => auth()->id(),
        'type'    => TransactionType::EXPENSE->value,
    ]);

    $tags = Tag::factory()->count(3)->create(['user_id' => auth()->id()]);

    $response = $this->post(route('transactions.store'), [
        'category_id'      => $category->id,
        'tags'             => $tags->pluck('id')->all(),
        'amount'           => 150.00,
        'type'             => TransactionType::EXPENSE->value,
        'description'      => 'Multi-tag transaction',
        'transaction_date' => '2026-01-15',
    ]);

    $response->assertRedirectBack();

    $transaction = Transaction::query()->where('description', 'Multi-tag transaction')->firstOrFail();
    expect($transaction->tags()->pluck('tags.id')->all())->toEqualCanonicalizing($tags->pluck('id')->all());
});

it('should sync tags when updating a transaction', function (): void {
    $category = Category::factory()->create([
        'user_id' => auth()->id(),
        'type'    => TransactionType::EXPENSE->value,
    ]);

    $initialTags = Tag::factory()->count(2)->create(['user_id' => auth()->id()]);
    $newTags = Tag::factory()->count(2)->create(['user_id' => auth()->id()]);

    $transaction = Transaction::factory()
        ->withTags($initialTags->pluck('id')->all())
        ->create([
            'user_id'     => auth()->id(),
            'category_id' => $category->id,
            'type'        => TransactionType::EXPENSE->value,
        ]);

    expect($transaction->tags()->count())->toBe(2);

    $this->put(route('transactions.update', $transaction->id), [
        'category_id'      => $category->id,
        'tags'             => $newTags->pluck('id')->all(),
        'amount'           => 100.00,
        'type'             => TransactionType::EXPENSE->value,
        'description'      => 'Updated description',
        'transaction_date' => '2026-01-20',
    ]);

    expect($transaction->fresh()->tags()->pluck('tags.id')->all())->toEqualCanonicalizing($newTags->pluck('id')->all());
});

// READ
it('should be able to view transactions index', function (): void {
    $response = $this->get(route('transactions.index'));

    $response->assertStatus(200);
});

it('should be able to view own transaction', function (): void {
    $transaction = Transaction::factory()->create([
        'user_id' => auth()->id(),
    ]);

    $response = $this->get(route('transactions.show', $transaction->id));

    $response->assertSuccessful();
});

it('should not be able to view transaction that you do not own', function (): void {
    $transaction = Transaction::factory()->create();

    $response = $this->get(route('transactions.show', $transaction->id));

    $response->assertForbidden();
});

// EDIT
it('should be able to edit transaction', function (): void {
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

    $response->assertRedirectBack();

    $this->assertDatabaseHas('transactions', [
        'id'          => $transaction->id,
        'amount'      => 200.00,
        'description' => 'Updated Salary',
    ]);
});

it('should not be able to edit transaction changing to type incompatible with category', function (): void {
    $category = Category::factory()->create([
        'user_id' => auth()->id(),
        'type'    => TransactionType::EXPENSE->value,
    ]);

    $transaction = Transaction::factory()->create([
        'user_id'     => auth()->id(),
        'category_id' => $category->id,
        'amount'      => 100.00,
        'type'        => TransactionType::EXPENSE->value,
        'description' => 'Grocery shopping',
    ]);

    $response = $this->put(route('transactions.update', $transaction->id), [
        'category_id'      => $category->id,
        'amount'           => 100.00,
        'type'             => TransactionType::INCOME->value,
        'description'      => 'Grocery shopping',
        'transaction_date' => '2026-01-20',
    ]);

    $response->assertSessionHasErrors('category_id');

    $this->assertDatabaseHas('transactions', [
        'id'   => $transaction->id,
        'type' => TransactionType::EXPENSE->value,
    ]);
});

it('should not be able to edit transaction that you do not own', function (): void {
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
it('should be able to delete transaction', function (): void {
    $transaction = Transaction::factory()->create([
        'user_id' => auth()->id(),
    ]);

    $response = $this->delete(route('transactions.destroy', $transaction->id));

    $response->assertRedirectBack();
});

it('should not be able to delete transaction that you do not own', function (): void {
    $transaction = Transaction::factory()->create();

    $response = $this->delete(route('transactions.destroy', $transaction->id));

    $response->assertStatus(403);
});
