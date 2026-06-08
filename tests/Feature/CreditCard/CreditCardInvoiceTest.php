<?php

declare(strict_types=1);

use App\Enums\TransactionType;
use App\Models\Category;
use App\Models\CreditCard;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\URL;

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);

    $this->category = Category::factory()->create([
        'user_id' => $this->user->id,
        'type'    => TransactionType::EXPENSE->value,
    ]);

    $this->card = CreditCard::factory()->create(['user_id' => $this->user->id]);
});

it('renders the invoice for the requested month with its total', function (): void {
    Transaction::factory()->create([
        'user_id'          => $this->user->id,
        'category_id'      => $this->category->id,
        'credit_card_id'   => $this->card->id,
        'type'             => TransactionType::EXPENSE->value,
        'amount'           => 12000,
        'transaction_date' => '2026-08-10',
    ]);

    $this->get(route('credit-cards.invoice', ['creditCard' => $this->card->id, 'month' => '2026-08']))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('CreditCardInvoice')
            ->where('year', 2026)
            ->where('month', 8)
            ->where('total', 12000)
            ->where('shared', false)
            ->has('transactions', 1)
        );
});

it("forbids viewing another user's invoice", function (): void {
    $otherCard = CreditCard::factory()->create();

    $this->get(route('credit-cards.invoice', ['creditCard' => $otherCard->id]))
        ->assertForbidden();
});

it('flashes a signed share url for the invoice', function (): void {
    $this->post(route('credit-cards.invoice.share', ['creditCard' => $this->card->id]), [
        'month' => '2026-08',
    ])->assertRedirect();

    expect(session('credit_card_invoice_share_url'))
        ->toBeString()
        ->toContain('/credit-card-invoice/' . $this->card->id)
        ->toContain('signature=');
});

it('serves the public receipt through a valid signed url', function (): void {
    Date::setTestNow('2026-08-15');

    $url = URL::temporarySignedRoute(
        'credit-cards.invoice.receipt',
        Date::now()->addDays(7),
        ['creditCard' => $this->card->id, 'month' => '2026-08'],
    );

    auth()->logout();

    $this->get($url)
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('CreditCardInvoice')
            ->where('shared', true)
        );

    Date::setTestNow();
});

it('rejects the public receipt without a valid signature', function (): void {
    auth()->logout();

    $this->get(route('credit-cards.invoice.receipt', ['creditCard' => $this->card->id, 'month' => '2026-08']))
        ->assertForbidden();
});
