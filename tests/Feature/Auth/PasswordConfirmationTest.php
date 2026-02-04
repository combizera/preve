<?php

declare(strict_types=1);

use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

it('should be able to render confirm password screen', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('password.confirm'));

    $response->assertOk();

    $response->assertInertia(
        fn (Assert $page) => $page
            ->component('auth/ConfirmPassword')
    );
});

it('should be able to require authentication for password confirmation', function () {
    $response = $this->get(route('password.confirm'));

    $response->assertRedirect(route('login'));
});
