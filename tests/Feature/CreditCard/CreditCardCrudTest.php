<?php

declare(strict_types=1);

use App\Enums\AccentColor;
use App\Models\CreditCard;
use App\Models\Transaction;
use App\Models\User;

beforeEach(function (): void {
    $this->actingAs(User::factory()->create());
});

it('renders the credit card index', function (): void {
    $this->get(route('credit-cards.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('CreditCard')
            ->has('creditCards')
            ->has('summary')
            ->has('upcomingInvoices')
        );
});

it('creates a credit card', function (): void {
    $this->post(route('credit-cards.store'), [
        'name'        => 'Nubank',
        'last_four'   => '1234',
        'closing_day' => 20,
        'due_day'     => 1,
        'color'       => AccentColor::VIOLET->value,
    ])->assertRedirect(route('credit-cards.index'));

    $this->assertDatabaseHas('credit_cards', [
        'user_id'     => auth()->id(),
        'name'        => 'Nubank',
        'closing_day' => 20,
        'due_day'     => 1,
    ]);
});

it('rejects an out-of-range closing day', function (): void {
    $this->post(route('credit-cards.store'), [
        'name'        => 'X',
        'closing_day' => 40,
        'due_day'     => 1,
        'color'       => AccentColor::BLUE->value,
    ])->assertSessionHasErrors('closing_day');
});

it('updates own credit card', function (): void {
    $card = CreditCard::factory()->create(['user_id' => auth()->id()]);

    $this->put(route('credit-cards.update', $card), [
        'name'        => 'Updated',
        'closing_day' => 5,
        'due_day'     => 15,
        'color'       => AccentColor::AMBER->value,
    ])->assertRedirect(route('credit-cards.index'));

    $this->assertDatabaseHas('credit_cards', [
        'id'          => $card->id,
        'name'        => 'Updated',
        'closing_day' => 5,
    ]);
});

it("forbids updating someone else's card", function (): void {
    $card = CreditCard::factory()->create();

    $this->put(route('credit-cards.update', $card), [
        'name'        => 'X',
        'closing_day' => 5,
        'due_day'     => 15,
        'color'       => AccentColor::AMBER->value,
    ])->assertForbidden();
});

it('deletes own card', function (): void {
    $card = CreditCard::factory()->create(['user_id' => auth()->id()]);

    $this->delete(route('credit-cards.destroy', $card))->assertRedirect(route('credit-cards.index'));
    $this->assertDatabaseMissing('credit_cards', ['id' => $card->id]);
});

it("forbids deleting someone else's card", function (): void {
    $card = CreditCard::factory()->create();

    $this->delete(route('credit-cards.destroy', $card))->assertForbidden();
});

it('nulls the card on its transactions when the card is deleted', function (): void {
    $card = CreditCard::factory()->create(['user_id' => auth()->id()]);
    $transaction = Transaction::factory()->create([
        'user_id'        => auth()->id(),
        'credit_card_id' => $card->id,
    ]);

    $card->delete();

    $this->assertDatabaseHas('transactions', [
        'id'             => $transaction->id,
        'credit_card_id' => null,
    ]);
});
