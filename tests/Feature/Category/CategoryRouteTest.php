<?php

declare(strict_types=1);

use App\Models\User;

beforeEach(function (): void {
    $user = User::factory()->create();
    $this->actingAs($user);
});

it('should be able to render Category screen', function (): void {
    $response = $this->get(route('categories.index'));
    $response->assertOk();
});
