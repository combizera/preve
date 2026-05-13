<?php

declare(strict_types=1);

it('should redirect to login page', function (): void {
    $response = $this->get('/');

    $response->assertRedirect('login');
});
