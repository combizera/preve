<?php

declare(strict_types=1);

use App\Enums\TransactionType;
use App\Models\Category;
use App\Models\CreditCard;
use App\Models\SavingsBucket;
use App\Models\Transaction;
use App\Models\User;

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);

    $this->category = Category::factory()->create([
        'user_id' => $this->user->id,
        'type'    => TransactionType::EXPENSE->value,
    ]);

    $this->card = CreditCard::factory()->create([
        'user_id'     => $this->user->id,
        'closing_day' => 20,
        'due_day'     => 1,
    ]);
});

it('moves selected expenses onto the card and rebills them on the due date', function (): void {
    $transaction = Transaction::factory()->create([
        'user_id'          => $this->user->id,
        'category_id'      => $this->category->id,
        'type'             => TransactionType::EXPENSE->value,
        'transaction_date' => '2026-06-10',
    ]);

    $this->patch(route('transactions.bulk-credit-card'), [
        'ids'            => [$transaction->id],
        'credit_card_id' => $this->card->id,
    ])->assertRedirect();

    $transaction->refresh();

    expect($transaction->credit_card_id)->toBe($this->card->id)
        ->and($transaction->purchase_date->toDateString())->toBe('2026-06-10')
        ->and($transaction->transaction_date->toDateString())->toBe('2026-07-01');
});

it('does not move income or savings-bucket transactions onto the card', function (): void {
    $incomeCategory = Category::factory()->create([
        'user_id' => $this->user->id,
        'type'    => TransactionType::INCOME->value,
    ]);
    $bucket = SavingsBucket::factory()->create(['user_id' => $this->user->id]);

    $income = Transaction::factory()->create([
        'user_id'     => $this->user->id,
        'category_id' => $incomeCategory->id,
        'type'        => TransactionType::INCOME->value,
    ]);
    $invested = Transaction::factory()->create([
        'user_id'           => $this->user->id,
        'category_id'       => $this->category->id,
        'type'              => TransactionType::EXPENSE->value,
        'savings_bucket_id' => $bucket->id,
    ]);

    $this->patch(route('transactions.bulk-credit-card'), [
        'ids'            => [$income->id, $invested->id],
        'credit_card_id' => $this->card->id,
    ])->assertRedirect();

    expect($income->refresh()->credit_card_id)->toBeNull()
        ->and($invested->refresh()->credit_card_id)->toBeNull();
});

it('rejects assigning a card the user does not own', function (): void {
    $transaction = Transaction::factory()->create([
        'user_id'     => $this->user->id,
        'category_id' => $this->category->id,
        'type'        => TransactionType::EXPENSE->value,
    ]);
    $otherCard = CreditCard::factory()->create();

    $this->patch(route('transactions.bulk-credit-card'), [
        'ids'            => [$transaction->id],
        'credit_card_id' => $otherCard->id,
    ])->assertSessionHasErrors('credit_card_id');

    expect($transaction->refresh()->credit_card_id)->toBeNull();
});
