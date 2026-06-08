<?php

declare(strict_types=1);

use App\Enums\TransactionType;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
    $this->category = Category::factory()->create(['user_id' => $this->user->id]);
});

function makeTransaction(User $user, Category $category): Transaction
{
    return Transaction::factory()->create([
        'user_id'     => $user->id,
        'category_id' => $category->id,
        'type'        => TransactionType::EXPENSE->value,
    ]);
}

it('deletes the selected transactions in bulk', function (): void {
    $a = makeTransaction($this->user, $this->category);
    $b = makeTransaction($this->user, $this->category);
    $keep = makeTransaction($this->user, $this->category);

    $this->delete(route('transactions.bulk-destroy'), [
        'ids' => [$a->id, $b->id],
    ])->assertRedirect();

    $this->assertDatabaseMissing('transactions', ['id' => $a->id]);
    $this->assertDatabaseMissing('transactions', ['id' => $b->id]);
    $this->assertDatabaseHas('transactions', ['id' => $keep->id]);
});

it('requires at least one id', function (): void {
    $this->delete(route('transactions.bulk-destroy'), ['ids' => []])
        ->assertSessionHasErrors('ids');
});

it("does not delete another user's transactions", function (): void {
    $mine = makeTransaction($this->user, $this->category);
    $other = Transaction::factory()->create(['type' => TransactionType::EXPENSE->value]);

    $this->delete(route('transactions.bulk-destroy'), [
        'ids' => [$mine->id, $other->id],
    ])->assertSessionHasErrors('ids.1');

    $this->assertDatabaseHas('transactions', ['id' => $mine->id]);
    $this->assertDatabaseHas('transactions', ['id' => $other->id]);
});
