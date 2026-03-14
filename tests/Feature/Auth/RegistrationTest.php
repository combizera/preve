<?php

declare(strict_types=1);

it('should be able to render registration screen', function (): void {
    $response = $this->get(route('register'));

    $response->assertOk();
});

it('should be able to register new users', function (): void {
    $response = $this->post(route('register.store'), [
        'name'                  => 'Test User',
        'email'                 => 'test@example.com',
        'password'              => 'password',
        'password_confirmation' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));
});
