<?php

declare(strict_types=1);

use App\Models\Tag;
use App\Models\Transaction;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

it('filters transactions by tag without crashing on the uuid join', function (): void {
    $tag = Tag::factory()->create(['user_id' => $this->user->id]);
    $otherTag = Tag::factory()->create(['user_id' => $this->user->id]);

    $tagged = Transaction::factory()
        ->withTags([$tag->id])
        ->create([
            'user_id'          => $this->user->id,
            'transaction_date' => now(),
        ]);

    Transaction::factory()
        ->withTags([$otherTag->id])
        ->create([
            'user_id'          => $this->user->id,
            'transaction_date' => now(),
        ]);

    $this->get(route('transactions.index', ['tags' => [$tag->id]]))
        ->assertSuccessful()
        ->assertInertia(
            fn (Assert $page): Assert => $page
                ->component('transactions/Transaction')
                ->has('transactions', 1)
                ->where('transactions.0.id', $tagged->id)
        );
});
