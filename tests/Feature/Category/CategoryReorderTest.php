<?php

declare(strict_types=1);

use App\Enums\TransactionType;
use App\Models\Category;
use App\Models\User;

beforeEach(function (): void {
    $user = User::factory()->create();
    $this->actingAs($user);
    Category::query()->where('user_id', $user->id)->delete();
});

it('auto-assigns sequential order per (user, type) on create', function (): void {
    $userId = auth()->id();

    $first = Category::factory()->create([
        'user_id' => $userId,
        'type'    => TransactionType::EXPENSE->value,
    ]);
    $second = Category::factory()->create([
        'user_id' => $userId,
        'type'    => TransactionType::EXPENSE->value,
    ]);
    $income = Category::factory()->create([
        'user_id' => $userId,
        'type'    => TransactionType::INCOME->value,
    ]);

    expect($first->fresh()->order)->toBeGreaterThan(0);
    expect($second->fresh()->order)->toBe($first->fresh()->order + 1);
    expect($income->fresh()->order)->toBeGreaterThan(0);
});

it('returns categories ordered by `order` ascending on index', function (): void {
    $userId = auth()->id();

    $a = Category::factory()->create(['user_id' => $userId, 'type' => TransactionType::EXPENSE->value]);
    $b = Category::factory()->create(['user_id' => $userId, 'type' => TransactionType::EXPENSE->value]);
    $c = Category::factory()->create(['user_id' => $userId, 'type' => TransactionType::EXPENSE->value]);

    Category::query()->whereKey($a->id)->update(['order' => 30]);
    Category::query()->whereKey($b->id)->update(['order' => 10]);
    Category::query()->whereKey($c->id)->update(['order' => 20]);

    $response = $this->get(route('categories.index'));

    $response->assertOk();

    $expense = collect($response->viewData('page')['props']['expenseCategories']);
    expect($expense->pluck('id')->all())->toBe([$b->id, $c->id, $a->id]);
});

it("reorders the authenticated user's categories within a type", function (): void {
    $userId = auth()->id();

    $a = Category::factory()->create(['user_id' => $userId, 'type' => TransactionType::EXPENSE->value]);
    $b = Category::factory()->create(['user_id' => $userId, 'type' => TransactionType::EXPENSE->value]);
    $c = Category::factory()->create(['user_id' => $userId, 'type' => TransactionType::EXPENSE->value]);

    $response = $this->patch(route('categories.reorder'), [
        'type' => 'expense',
        'ids'  => [$c->id, $a->id, $b->id],
    ]);

    $response->assertRedirect(route('categories.index'));

    expect($c->fresh()->order)->toBe(1);
    expect($a->fresh()->order)->toBe(2);
    expect($b->fresh()->order)->toBe(3);
});

it('rejects reorder when an id belongs to another user', function (): void {
    $userId = auth()->id();
    $mine = Category::factory()->create(['user_id' => $userId, 'type' => TransactionType::EXPENSE->value]);
    $foreign = Category::factory()->create(['type' => TransactionType::EXPENSE->value]);

    $response = $this->patch(route('categories.reorder'), [
        'type' => 'expense',
        'ids'  => [$mine->id, $foreign->id],
    ]);

    $response->assertSessionHasErrors('ids.1');
});

it('rejects reorder when an id has a different type', function (): void {
    $userId = auth()->id();
    $expense = Category::factory()->create(['user_id' => $userId, 'type' => TransactionType::EXPENSE->value]);
    $income = Category::factory()->create(['user_id' => $userId, 'type' => TransactionType::INCOME->value]);

    $response = $this->patch(route('categories.reorder'), [
        'type' => 'expense',
        'ids'  => [$expense->id, $income->id],
    ]);

    $response->assertSessionHasErrors('ids.1');
});

it('rejects reorder with invalid type', function (): void {
    $response = $this->patch(route('categories.reorder'), [
        'type' => 'something',
        'ids'  => [1],
    ]);

    $response->assertSessionHasErrors('type');
});

it('rejects reorder with duplicate ids', function (): void {
    $userId = auth()->id();
    $a = Category::factory()->create(['user_id' => $userId, 'type' => TransactionType::EXPENSE->value]);

    $response = $this->patch(route('categories.reorder'), [
        'type' => 'expense',
        'ids'  => [$a->id, $a->id],
    ]);

    $response->assertSessionHasErrors('ids.1');
});

it('rejects reorder when ids do not cover the full set for the type', function (): void {
    $userId = auth()->id();
    $a = Category::factory()->create(['user_id' => $userId, 'type' => TransactionType::EXPENSE->value]);
    $b = Category::factory()->create(['user_id' => $userId, 'type' => TransactionType::EXPENSE->value]);

    $response = $this->patch(route('categories.reorder'), [
        'type' => 'expense',
        'ids'  => [$a->id],
    ]);

    $response->assertSessionHasErrors('ids');

    expect($b->fresh()->order)->not->toBe(0);
});
