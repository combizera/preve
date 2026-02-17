<?php

declare(strict_types=1);

namespace App\Http\Controllers\Concerns;

use App\Support\Toast;

trait HasToast
{
    public function __construct(public Toast $toast) {}
}
