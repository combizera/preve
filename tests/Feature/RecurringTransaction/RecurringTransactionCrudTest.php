<?php

declare(strict_types=1);

use App\Enums\FrequencyType;
use App\Enums\TransactionType;
use App\Models\Category;
use App\Models\RecurringTransaction;
use App\Models\Tag;
use App\Models\User;

beforeEach(function (): void {
    $user = User::factory()->create();
    $this->actingAs($user);
});

// CREATE
it('should be able to create recurring transaction', function (): void {
    $category = Category::factory()->create([
        'user_id' => auth()->id(),
        'type'    => TransactionType::EXPENSE->value,
    ]);

    $response = $this->post(route('recurring.store'), [
        'category_id'  => $category->id,
        'amount'       => 9990,
        'type'         => TransactionType::EXPENSE->value,
        'frequency'    => FrequencyType::MONTHLY->value,
        'description'  => 'Internet Service',
        'is_active'    => true,
        'day_of_month' => 20,
        'start_date'   => '2026-01-01',
        'end_date'     => null,
    ]);

    $response->assertRedirect(route('recurring.index'));

    $this->assertDatabaseHas('recurring_transactions', [
        'category_id'  => $category->id,
        'amount'       => 9990,
        'type'         => TransactionType::EXPENSE->value,
        'frequency'    => FrequencyType::MONTHLY->value,
        'description'  => 'Internet Service',
        'day_of_month' => 20,
    ]);
});

it('should derive day_of_month from start_date when frequency is yearly', function (): void {
    $category = Category::factory()->create([
        'user_id' => auth()->id(),
        'type'    => TransactionType::EXPENSE->value,
    ]);

    $response = $this->post(route('recurring.store'), [
        'category_id'  => $category->id,
        'amount'       => 50000,
        'type'         => TransactionType::EXPENSE->value,
        'frequency'    => FrequencyType::YEARLY->value,
        'description'  => 'Annual Subscription',
        'is_active'    => true,
        'day_of_month' => 1,
        'start_date'   => '2026-08-22',
        'end_date'     => null,
    ]);

    $response->assertRedirect(route('recurring.index'));

    $this->assertDatabaseHas('recurring_transactions', [
        'description'  => 'Annual Subscription',
        'frequency'    => FrequencyType::YEARLY->value,
        'day_of_month' => 22,
    ]);
});

it('should be able to store a recurring transaction and automatically generate future projections', function (): void {
    $category = Category::factory()->create(['user_id' => auth()->id(), 'type' => 'expense']);

    $payload = [
        'category_id'  => $category->id,
        'amount'       => 15000,
        'frequency'    => 'monthly',
        'type'         => 'expense',
        'description'  => 'New Streaming Account',
        'is_active'    => true,
        'day_of_month' => now()->day,
        'start_date'   => now()->format('Y-m-d'),
    ];

    $response = $this->post(route('recurring.store'), $payload);

    $response->assertRedirect(route('recurring.index'));

    $this->assertDatabaseHas('recurring_transactions', [
        'description' => 'New Streaming Account',
    ]);

    $this->assertDatabaseCount('transactions', 4);
});

it('should not be able to create recurring transaction with zero or negative amount', function (int|float $amount): void {
    $category = Category::factory()->create([
        'user_id' => auth()->id(),
        'type'    => TransactionType::EXPENSE->value,
    ]);

    $response = $this->post(route('recurring.store'), [
        'category_id'  => $category->id,
        'amount'       => $amount,
        'type'         => TransactionType::EXPENSE->value,
        'frequency'    => FrequencyType::MONTHLY->value,
        'description'  => 'Internet Service',
        'is_active'    => true,
        'day_of_month' => 20,
        'start_date'   => '2026-01-01',
        'end_date'     => null,
    ]);

    $response->assertSessionHasErrors('amount');

    $this->assertDatabaseMissing('recurring_transactions', [
        'category_id' => $category->id,
        'description' => 'Internet Service',
    ]);
})->with([
    'zero'     => 0,
    'negative' => -100,
]);

it('should attach tags and propagate them to generated transactions', function (): void {
    $category = Category::factory()->create([
        'user_id' => auth()->id(),
        'type'    => TransactionType::EXPENSE->value,
    ]);

    $tags = Tag::factory()->count(2)->create(['user_id' => auth()->id()]);

    $response = $this->post(route('recurring.store'), [
        'category_id'  => $category->id,
        'tags'         => $tags->pluck('id')->all(),
        'amount'       => 9990,
        'type'         => TransactionType::EXPENSE->value,
        'frequency'    => FrequencyType::MONTHLY->value,
        'description'  => 'Tagged recurring',
        'is_active'    => true,
        'day_of_month' => now()->day,
        'start_date'   => now()->format('Y-m-d'),
    ]);

    $response->assertRedirect(route('recurring.index'));

    $recurring = RecurringTransaction::query()->where('description', 'Tagged recurring')->firstOrFail();
    expect($recurring->tags()->pluck('tags.id')->all())->toEqualCanonicalizing($tags->pluck('id')->all());

    $generatedTransaction = $recurring->transactions()->first();
    expect($generatedTransaction)->not->toBeNull()
        ->and($generatedTransaction->tags()->pluck('tags.id')->all())->toEqualCanonicalizing($tags->pluck('id')->all());
});

// READ
it('should be able to view recurring transactions index', function (): void {
    $response = $this->get(route('recurring.index'));

    $response->assertStatus(200);
});

// EDIT
it('should be able to edit recurring transaction', function (): void {
    $category = Category::factory()->create([
        'user_id' => auth()->id(),
        'type'    => TransactionType::EXPENSE->value,
    ]);

    $recurring = RecurringTransaction::factory()->create([
        'user_id'      => auth()->id(),
        'category_id'  => $category->id,
        'amount'       => 9990,
        'type'         => TransactionType::EXPENSE->value,
        'frequency'    => FrequencyType::MONTHLY->value,
        'description'  => 'Internet Service',
        'day_of_month' => 20,
    ]);

    $response = $this->put(route('recurring.update', $recurring->id), [
        'category_id'  => $category->id,
        'amount'       => 12990,
        'type'         => TransactionType::EXPENSE->value,
        'frequency'    => FrequencyType::MONTHLY->value,
        'description'  => 'Updated Internet Service',
        'day_of_month' => 15,
        'start_date'   => '2026-01-01',
    ]);

    $response->assertRedirect(route('recurring.index'));

    $this->assertDatabaseHas('recurring_transactions', [
        'id'           => $recurring->id,
        'amount'       => 12990,
        'description'  => 'Updated Internet Service',
        'day_of_month' => 15,
    ]);
});

it('should not be able to edit recurring transaction that you do not own', function (): void {
    $recurring = RecurringTransaction::factory()->create();

    $category = Category::factory()->create([
        'user_id' => auth()->id(),
        'type'    => TransactionType::EXPENSE->value,
    ]);

    $response = $this->put(route('recurring.update', $recurring->id), [
        'category_id'  => $category->id,
        'amount'       => 20000,
        'type'         => TransactionType::EXPENSE->value,
        'frequency'    => FrequencyType::MONTHLY->value,
        'description'  => 'Updated Recurring',
        'day_of_month' => 10,
        'start_date'   => '2026-01-01',
    ]);

    $response->assertStatus(403);
});

// DELETE
it('should be able to delete recurring transaction', function (): void {
    $recurring = RecurringTransaction::factory()->create([
        'user_id' => auth()->id(),
    ]);

    $response = $this->delete(route('recurring.destroy', $recurring->id));

    $response->assertRedirect(route('recurring.index'));
});

it('should not be able to delete recurring transaction that you do not own', function (): void {
    $recurring = RecurringTransaction::factory()->create();

    $response = $this->delete(route('recurring.destroy', $recurring->id));

    $response->assertStatus(403);
});
