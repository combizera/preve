<?php

declare(strict_types=1);

use App\Models\RecurringTransaction;
use App\Models\User;

beforeEach(function (): void {
    $user = User::factory()->create();
    $this->actingAs($user);
});

// TOGGLE
it('should be able to toggle recurring transaction active status', function (): void {
    $recurring = RecurringTransaction::factory()->create([
        'user_id'   => auth()->id(),
        'is_active' => true,
    ]);

    $response = $this->patch(route('recurring.toggle', $recurring->id));

    $response->assertRedirect(route('recurring.index'));

    $this->assertDatabaseHas('recurring_transactions', [
        'id'        => $recurring->id,
        'is_active' => false,
    ]);
});

it('should not be able to toggle recurring transaction that you do not own', function (): void {
    $recurring = RecurringTransaction::factory()->create();

    $response = $this->patch(route('recurring.toggle', $recurring->id));

    $response->assertStatus(403);
});
