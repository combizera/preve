<?php

declare(strict_types=1);

use App\Enums\CategoryColor;
use App\Enums\CategoryIcon;
use App\Enums\TransactionType;
use App\Models\Category;
use App\Models\User;

beforeEach(function (): void {
    $user = User::factory()->create();
    $this->actingAs($user);
});

// CREATE
it('should be able to create category', function (): void {
    $response = $this->post(route('categories.store'), [
        'name'  => 'New Category',
        'color' => CategoryColor::BLUE->value,
        'icon'  => CategoryIcon::BOOK->value,
        'type'  => TransactionType::EXPENSE->value,
    ]);

    $response->assertRedirect(route('categories.index'));

    $this->assertDatabaseHas('categories', [
        'name'  => 'New Category',
        'color' => CategoryColor::BLUE->value,
        'icon'  => CategoryIcon::BOOK->value,
        'type'  => TransactionType::EXPENSE->value,
    ]);
});

// READ
it('should be able to view categories index', function (): void {
    $response = $this->get(route('categories.index'));

    $response->assertStatus(200);
});

// EDIT
it('should be able to edit category', function (): void {
    $category = Category::factory()->create([
        'user_id' => auth()->id(),
        'name'    => 'New Category',
        'color'   => CategoryColor::RED->value,
        'icon'    => CategoryIcon::CAR->value,
        'type'    => TransactionType::INCOME->value,
    ]);

    $response = $this->put(route('categories.update', $category->id), [
        'name' => 'Updated Category',
        'type' => TransactionType::INCOME->value,
    ]);

    $response->assertRedirect(route('categories.index'));

    $this->assertDatabaseHas('categories', [
        'name'  => 'Updated Category',
        'color' => CategoryColor::RED->value,
        'icon'  => CategoryIcon::CAR->value,
        'type'  => TransactionType::INCOME->value,
    ]);
});

it('shoud not be able to edit category that you do not own', function (): void {
    $category = Category::factory()->create();

    $response = $this->put(route('categories.update', $category->id), [
        'name' => 'Updated Category',
        'type' => TransactionType::INCOME->value,
    ]);

    $response->assertStatus(403);
});

// DELETE
it('should be able to delete category', function (): void {
    $category = Category::factory()->create([
        'user_id' => auth()->id(),
    ]);

    $response = $this->delete(route('categories.destroy', $category->id));

    $response->assertRedirect(route('categories.index'));
});

it('should not be able to delete category that you do not own', function (): void {
    $category = Category::factory()->create();

    $response = $this->delete(route('categories.destroy', $category->id));

    $response->assertStatus(403);
});
