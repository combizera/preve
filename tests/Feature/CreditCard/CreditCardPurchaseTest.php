<?php

declare(strict_types=1);

use App\Enums\FrequencyType;
use App\Enums\TransactionType;
use App\Models\Category;
use App\Models\CreditCard;
use App\Models\RecurringTransaction;
use App\Models\SavingsBucket;
use App\Models\Transaction;
use App\Models\User;
use App\Services\RecurringTransactionService;
use Illuminate\Support\Facades\Date;
use Illuminate\Testing\TestResponse;

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

function storePurchase(array $overrides = []): TestResponse
{
    return test()->post(route('transactions.store'), array_merge([
        'category_id'      => test()->category->id,
        'credit_card_id'   => test()->card->id,
        'amount'           => 120000,
        'type'             => TransactionType::EXPENSE->value,
        'description'      => 'Notebook',
        'transaction_date' => '2026-01-10',
        'splits'           => 1,
    ], $overrides));
}

it('books a single card purchase on the invoice due date and keeps the purchase date', function (): void {
    storePurchase()->assertRedirect();

    $transaction = Transaction::query()->firstOrFail();

    expect($transaction->transaction_date->toDateString())->toBe('2026-02-01')
        ->and($transaction->purchase_date->toDateString())->toBe('2026-01-10')
        ->and($transaction->amount)->toBe(120000)
        ->and($transaction->split_number)->toBeNull()
        ->and($transaction->split_total)->toBeNull();
});

it('fans an installment purchase into one transaction per future invoice', function (): void {
    storePurchase(['splits' => 6])->assertRedirect();

    $rows = Transaction::query()->oldest('transaction_date')->get();

    expect($rows)->toHaveCount(6)
        ->and($rows->sum('amount'))->toBe(120000)
        ->and($rows->pluck('amount')->all())->each->toBe(20000);

    expect($rows->pluck('transaction_date')->map->toDateString()->all())->toBe([
        '2026-02-01',
        '2026-03-01',
        '2026-04-01',
        '2026-05-01',
        '2026-06-01',
        '2026-07-01',
    ]);

    $parent = $rows->firstWhere('split_number', 1);

    expect($parent->parent_transaction_id)->toBeNull()
        ->and($rows->where('split_number', '>', 1)->pluck('parent_transaction_id')->unique()->all())
        ->toBe([$parent->id]);

    expect($rows->pluck('split_total')->unique()->all())->toBe([6]);
});

it('gives the remainder cents to the first installment', function (): void {
    storePurchase(['amount' => 100000, 'splits' => 3])->assertRedirect();

    $amounts = Transaction::query()->orderBy('split_number')->pluck('amount')->all();

    expect($amounts)->toBe([33334, 33333, 33333])
        ->and(array_sum($amounts))->toBe(100000);
});

it('rejects a transaction tied to both a card and a savings bucket', function (): void {
    $bucket = SavingsBucket::factory()->create(['user_id' => $this->user->id]);

    storePurchase(['savings_bucket_id' => $bucket->id])
        ->assertSessionHasErrors('credit_card_id');

    expect(Transaction::query()->count())->toBe(0);
});

it('rejects an income transaction tied to a card', function (): void {
    $incomeCategory = Category::factory()->create([
        'user_id' => $this->user->id,
        'type'    => TransactionType::INCOME->value,
    ]);

    storePurchase([
        'category_id' => $incomeCategory->id,
        'type'        => TransactionType::INCOME->value,
        'splits'      => 1,
    ])->assertSessionHasErrors('credit_card_id');

    expect(Transaction::query()->count())->toBe(0);
});

it('recomputes the effective date when updating a card transaction', function (): void {
    $transaction = Transaction::factory()->create([
        'user_id'     => $this->user->id,
        'category_id' => $this->category->id,
        'type'        => TransactionType::EXPENSE->value,
    ]);

    $this->put(route('transactions.update', $transaction), [
        'category_id'      => $this->category->id,
        'credit_card_id'   => $this->card->id,
        'amount'           => 5000,
        'type'             => TransactionType::EXPENSE->value,
        'description'      => 'Switched to card',
        'transaction_date' => '2026-03-25',
    ])->assertRedirect();

    $transaction->refresh();

    expect($transaction->transaction_date->toDateString())->toBe('2026-05-01')
        ->and($transaction->purchase_date->toDateString())->toBe('2026-03-25');
});

it('reports the committed amount and upcoming invoices on the index', function (): void {
    Transaction::factory()->create([
        'user_id'          => $this->user->id,
        'category_id'      => $this->category->id,
        'credit_card_id'   => $this->card->id,
        'type'             => TransactionType::EXPENSE->value,
        'amount'           => 30000,
        'transaction_date' => now()->addMonth()->startOfMonth(),
    ]);

    $this->get(route('credit-cards.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('summary.committed', 30000)
            ->where('creditCards.0.committed', 30000)
            ->has('upcomingInvoices', 6)
        );
});

it('stores a recurring transaction tied to a card via the form', function (): void {
    Date::setTestNow('2026-01-05');

    $this->post(route('recurring.store'), [
        'category_id'    => $this->category->id,
        'credit_card_id' => $this->card->id,
        'amount'         => 8000,
        'type'           => TransactionType::EXPENSE->value,
        'frequency'      => FrequencyType::MONTHLY->value,
        'description'    => 'Streaming on card',
        'is_active'      => true,
        'day_of_month'   => 10,
        'start_date'     => '2026-01-01',
    ])->assertRedirect(route('recurring.index'));

    $recurring = RecurringTransaction::query()->firstOrFail();

    expect($recurring->credit_card_id)->toBe($this->card->id);

    $generated = $recurring->transactions()->first();

    expect($generated->credit_card_id)->toBe($this->card->id)
        ->and($generated->purchase_date->day)->toBe(10)
        ->and($generated->transaction_date->day)->toBe(1);

    Date::setTestNow();
});

it('generates recurring card charges on the invoice due date', function (): void {
    $recurring = RecurringTransaction::factory()->create([
        'user_id'        => $this->user->id,
        'category_id'    => $this->category->id,
        'credit_card_id' => $this->card->id,
        'type'           => TransactionType::EXPENSE->value,
        'frequency'      => FrequencyType::MONTHLY->value,
        'amount'         => 8000,
        'day_of_month'   => 10,
        'is_active'      => true,
        'start_date'     => '2026-01-01',
        'end_date'       => null,
    ]);

    Date::setTestNow('2026-01-05');

    resolve(RecurringTransactionService::class)->generateFutureTransactions($recurring, 2);

    $generated = $recurring->transactions()->oldest('transaction_date')->get();

    expect($generated)->not->toBeEmpty();

    $first = $generated->first();

    expect($first->purchase_date->day)->toBe(10)
        ->and($first->transaction_date->day)->toBe(1)
        ->and($first->credit_card_id)->toBe($this->card->id);

    Date::setTestNow();
});
