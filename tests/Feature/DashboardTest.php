<?php

declare(strict_types=1);

use App\Models\User;

it('should be able to redirect guests to the login page', function () {
    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('login'));
});

it('should be able to visit the dashboard as authenticated user', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('dashboard'));
    $response->assertOk();
});
