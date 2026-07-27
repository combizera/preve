<?php

declare(strict_types=1);

use App\Enums\SupportedCurrency;
use App\Enums\TransactionType;
use App\Models\Hiatus;
use App\Models\RecurringTransaction;
use App\Models\Transaction;
use App\Models\User;
use App\Notifications\ReengagementNotification;
use App\Services\InactivityService;
use Illuminate\Support\Facades\Notification;

beforeEach(function (): void {
    Notification::fake();
});

function makeInactiveUser(int $daysAgo = 45): User
{
    $user = User::factory()->create();

    Transaction::factory()->create([
        'user_id'    => $user->id,
        'created_at' => now()->subDays($daysAgo),
    ]);

    return $user;
}

it('notifies inactive verified users and stamps the timestamp', function (): void {
    $user = makeInactiveUser();

    $this->artisan('users:notify-inactive')->assertSuccessful();

    Notification::assertSentTo($user, ReengagementNotification::class);
    expect($user->refresh()->reengagement_notified_at)->not->toBeNull();
});

it('skips recently active users', function (): void {
    $user = User::factory()->create();
    Transaction::factory()->create([
        'user_id'    => $user->id,
        'created_at' => now()->subDays(3),
    ]);

    $this->artisan('users:notify-inactive')->assertSuccessful();

    Notification::assertNothingSent();
});

it('skips users who never created a transaction', function (): void {
    User::factory()->create();

    $this->artisan('users:notify-inactive')->assertSuccessful();

    Notification::assertNothingSent();
});

it('skips unverified users', function (): void {
    $user = makeInactiveUser();
    $user->forceFill(['email_verified_at' => null])->save();

    $this->artisan('users:notify-inactive')->assertSuccessful();

    Notification::assertNothingSent();
});

it('skips users on an active hiatus', function (): void {
    $user = makeInactiveUser();
    Hiatus::factory()->open()->create([
        'user_id'    => $user->id,
        'started_at' => now()->subDays(10)->toDateString(),
    ]);

    $this->artisan('users:notify-inactive')->assertSuccessful();

    Notification::assertNothingSent();
});

it('respects the cooldown window between emails', function (): void {
    $user = makeInactiveUser();
    $user->forceFill(['reengagement_notified_at' => now()->subDays(10)])->save();

    $this->artisan('users:notify-inactive')->assertSuccessful();

    Notification::assertNothingSent();
});

it('notifies again once the cooldown expires', function (): void {
    $user = makeInactiveUser(90);
    $user->forceFill(['reengagement_notified_at' => now()->subDays(31)])->save();

    $this->artisan('users:notify-inactive')->assertSuccessful();

    Notification::assertSentTo($user, ReengagementNotification::class);
});

it('sends nothing on a dry run', function (): void {
    $user = makeInactiveUser();

    $this->artisan('users:notify-inactive --dry-run')
        ->expectsOutputToContain($user->email)
        ->assertSuccessful();

    Notification::assertNothingSent();
    expect($user->refresh()->reengagement_notified_at)->toBeNull();
});

it('respects a custom threshold via --days', function (): void {
    $user = makeInactiveUser(10);

    $this->artisan('users:notify-inactive --days=7')->assertSuccessful();

    Notification::assertSentTo($user, ReengagementNotification::class);
});

it('skips users who recently completed the welcome-back flow', function (): void {
    $user = makeInactiveUser();
    Hiatus::factory()->create([
        'user_id'       => $user->id,
        'started_at'    => now()->subDays(45)->toDateString(),
        'ended_at'      => now()->subDays(2)->toDateString(),
        'reconciled_at' => now()->subDays(2),
    ]);

    $this->artisan('users:notify-inactive')->assertSuccessful();

    Notification::assertNothingSent();
});

it('excludes pre-generated future charges from the recurring total', function (): void {
    $user = makeInactiveUser();
    $recurring = RecurringTransaction::factory()->create(['user_id' => $user->id]);
    Transaction::factory()->create([
        'user_id'                  => $user->id,
        'recurring_transaction_id' => $recurring->id,
        'type'                     => TransactionType::EXPENSE,
        'amount'                   => 70000,
        'transaction_date'         => now()->addMonths(2)->toDateString(),
        'created_at'               => now()->subDays(5),
    ]);

    $service = new InactivityService();
    $total = $service->recurringExpensesSince($user, $service->lastManualActivity($user));

    expect($total)->toBe(0);
});

it('resets the notified timestamp when the user registers a manual transaction', function (): void {
    $user = makeInactiveUser();
    $user->forceFill(['reengagement_notified_at' => now()->subDays(5)])->save();

    Transaction::factory()->create(['user_id' => $user->id]);

    expect($user->refresh()->reengagement_notified_at)->toBeNull();
});

it('builds a localized mail pointing to the welcome-back flow', function (): void {
    $user = makeInactiveUser();
    $user->forceFill(['locale' => 'pt_BR', 'currency' => SupportedCurrency::BRL])->save();

    $recurring = RecurringTransaction::factory()->create(['user_id' => $user->id]);
    Transaction::factory()->create([
        'user_id'                  => $user->id,
        'recurring_transaction_id' => $recurring->id,
        'type'                     => TransactionType::EXPENSE,
        'amount'                   => 250000,
        'created_at'               => now()->subDays(5),
    ]);

    app()->setLocale($user->preferredLocale());

    $rendered = (new ReengagementNotification())->toMail($user->refresh());

    expect($rendered->actionUrl)->toBe(route('reconciliation.show'))
        ->and($rendered->subject)->toBe('Faz 45 dias que você não registra nada no Preve')
        ->and(collect($rendered->introLines)->join(' '))->toContain('R$ 2.500,00');
});
