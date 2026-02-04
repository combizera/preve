<?php

declare(strict_types=1);

use App\Enums\CategoryColor;
use App\Enums\CategoryIcon;
use App\Enums\TransactionType;
use App\Models\Category;
use App\Models\User;

beforeEach(function () {
    $user = User::factory()->create();
    $this->actingAs($user);
});

it('should be able to create category', function () {
    $response = $this->post(route('categories.store'), [
        'name'  => 'New Category',
        'slug'  => 'new-category',
        'color' => CategoryColor::BLUE->value,
        'icon'  => CategoryIcon::BOOK->value,
        'type'  => TransactionType::EXPENSE->value,
    ]);

    $response->assertRedirect(route('categories.index'));

    $this->assertDatabaseHas('categories', [
        'name'  => 'New Category',
        'slug'  => 'new-category',
        'color' => CategoryColor::BLUE->value,
        'icon'  => CategoryIcon::BOOK->value,
        'type'  => TransactionType::EXPENSE->value,
    ]);
});

it('should be able to edit category', function () {
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
