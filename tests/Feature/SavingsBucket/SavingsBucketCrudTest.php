<?php

declare(strict_types=1);

use App\Enums\AccentColor;
use App\Enums\SavingsBucketIcon;
use App\Enums\TransactionType;
use App\Models\Category;
use App\Models\SavingsBucket;
use App\Models\Transaction;
use App\Models\User;

beforeEach(function (): void {
    $user = User::factory()->create();
    $this->actingAs($user);
});

it('renders the savings index', function (): void {
    $this->get(route('savings.index'))->assertOk();
});

it('creates a savings bucket', function (): void {
    $response = $this->post(route('savings.store'), [
        'name'          => 'Reserva de emergência',
        'target_amount' => 600000,
        'color'         => AccentColor::EMERALD->value,
        'icon'          => SavingsBucketIcon::PIGGY_BANK->value,
    ]);

    $response->assertRedirect(route('savings.index'));
    $this->assertDatabaseHas('savings_buckets', [
        'user_id'        => auth()->id(),
        'name'           => 'Reserva de emergência',
        'target_amount'  => 600000,
        'current_amount' => 0,
    ]);
});

it('creates a savings bucket with an initial amount without registering a transaction', function (): void {
    $this->post(route('savings.store'), [
        'name'           => 'Reserva',
        'target_amount'  => 600000,
        'initial_amount' => 1000000,
        'color'          => AccentColor::EMERALD->value,
        'icon'           => SavingsBucketIcon::PIGGY_BANK->value,
    ])->assertRedirect(route('savings.index'));

    $this->assertDatabaseHas('savings_buckets', [
        'user_id'        => auth()->id(),
        'name'           => 'Reserva',
        'current_amount' => 1000000,
    ]);

    expect(Transaction::query()->count())->toBe(0);
});

it('rejects creating with invalid target_amount', function (int $amount): void {
    $this->post(route('savings.store'), [
        'name'          => 'X',
        'target_amount' => $amount,
        'color'         => AccentColor::BLUE->value,
        'icon'          => SavingsBucketIcon::PIGGY_BANK->value,
    ])->assertSessionHasErrors('target_amount');
})->with([
    'zero'     => 0,
    'negative' => -10,
]);

it('updates own bucket', function (): void {
    $bucket = SavingsBucket::factory()->create(['user_id' => auth()->id()]);

    $this->put(route('savings.update', $bucket), [
        'name'          => 'Updated',
        'target_amount' => 999999,
        'color'         => AccentColor::AMBER->value,
        'icon'          => SavingsBucketIcon::PIGGY_BANK->value,
    ])->assertRedirect(route('savings.index'));

    $this->assertDatabaseHas('savings_buckets', [
        'id'            => $bucket->id,
        'name'          => 'Updated',
        'target_amount' => 999999,
    ]);
});

it("forbids updating someone else's bucket", function (): void {
    $bucket = SavingsBucket::factory()->create();

    $this->put(route('savings.update', $bucket), [
        'name'          => 'X',
        'target_amount' => 1,
        'color'         => AccentColor::AMBER->value,
        'icon'          => SavingsBucketIcon::PIGGY_BANK->value,
    ])->assertForbidden();
});

it('deletes an empty bucket', function (): void {
    $bucket = SavingsBucket::factory()->create([
        'user_id'        => auth()->id(),
        'current_amount' => 0,
    ]);

    $this->delete(route('savings.destroy', $bucket))->assertRedirect(route('savings.index'));
    $this->assertDatabaseMissing('savings_buckets', ['id' => $bucket->id]);
});

it('blocks deleting a bucket with balance', function (): void {
    $bucket = SavingsBucket::factory()->create([
        'user_id'        => auth()->id(),
        'current_amount' => 5000,
    ]);

    $this->delete(route('savings.destroy', $bucket))->assertSessionHasErrors('savings_bucket');
    $this->assertDatabaseHas('savings_buckets', ['id' => $bucket->id]);
});

it("forbids deleting someone else's bucket", function (): void {
    $bucket = SavingsBucket::factory()->create();

    $this->delete(route('savings.destroy', $bucket))->assertForbidden();
});

it('cascades delete to transactions when user is deleted', function (): void {
    $bucket = SavingsBucket::factory()->create(['user_id' => auth()->id()]);
    $category = Category::factory()->create([
        'user_id' => auth()->id(),
        'type'    => TransactionType::EXPENSE->value,
    ]);

    Transaction::factory()->create([
        'user_id'           => auth()->id(),
        'category_id'       => $category->id,
        'savings_bucket_id' => $bucket->id,
        'amount'            => 1000,
        'type'              => TransactionType::EXPENSE->value,
    ]);

    auth()->user()->delete();

    $this->assertDatabaseMissing('savings_buckets', ['id' => $bucket->id]);
});
