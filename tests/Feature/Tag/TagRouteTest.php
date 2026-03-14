<?php

declare(strict_types=1);

use App\Models\User;

beforeEach(function (): void {
    $user = User::factory()->create();
    $this->actingAs($user);
});

it('should be able to render Tag screen', function (): void {
    $response = $this->get(route('tags.index'));
    $response->assertOk();
});
