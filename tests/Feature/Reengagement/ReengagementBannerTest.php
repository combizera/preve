<?php

declare(strict_types=1);

use App\Models\Hiatus;
use App\Models\Transaction;
use App\Models\User;

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

function makeInactive(User $user, int $days = 45): void
{
    Transaction::factory()->create([
        'user_id'    => $user->id,
        'created_at' => now()->subDays($days),
    ]);
}

it('shares the banner payload for an inactive user', function (): void {
    makeInactive($this->user);

    $this->get(route('transactions.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('reengagement.inactive_days', 45)
            ->where('reengagement.last_manual_activity', now()->subDays(45)->toDateString())
        );
});

it('hides the banner for a recently active user', function (): void {
    Transaction::factory()->create([
        'user_id'    => $this->user->id,
        'created_at' => now()->subDays(3),
    ]);

    $this->get(route('transactions.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->where('reengagement', null));
});

it('hides the banner for a user who never created a transaction', function (): void {
    $this->get(route('transactions.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->where('reengagement', null));
});

it('hides the banner during an active hiatus', function (): void {
    makeInactive($this->user);
    Hiatus::factory()->open()->create([
        'user_id'    => $this->user->id,
        'started_at' => now()->subDays(20)->toDateString(),
    ]);

    $this->get(route('transactions.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->where('reengagement', null));
});

it('hides the banner after a recent dismissal', function (): void {
    makeInactive($this->user);
    $this->user->forceFill(['reengagement_banner_dismissed_at' => now()->subDays(2)])->save();

    $this->get(route('transactions.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->where('reengagement', null));
});

it('shows the banner again after the dismissal window expires', function (): void {
    makeInactive($this->user);
    $this->user->forceFill(['reengagement_banner_dismissed_at' => now()->subDays(8)])->save();

    $this->get(route('transactions.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->where('reengagement.inactive_days', 45));
});

it('dismisses the banner via the endpoint', function (): void {
    makeInactive($this->user);

    $this->post(route('reengagement.dismiss'))->assertRedirect();

    expect($this->user->refresh()->reengagement_banner_dismissed_at)->not->toBeNull();

    $this->get(route('transactions.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->where('reengagement', null));
});

it('hides the banner immediately after a manual transaction is created', function (): void {
    makeInactive($this->user);

    $this->get(route('transactions.index'))
        ->assertInertia(fn ($page) => $page->where('reengagement.inactive_days', 45));

    Transaction::factory()->create(['user_id' => $this->user->id]);

    $this->get(route('transactions.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->where('reengagement', null));
});

it('does not share a banner payload for guests', function (): void {
    auth()->logout();

    $this->get(route('login'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->where('reengagement', null));
});
