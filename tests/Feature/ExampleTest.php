<?php

declare(strict_types=1);

it('should be able to return a successful response', function () {
    $response = $this->get(route('home'));

    $response->assertOk();
});
