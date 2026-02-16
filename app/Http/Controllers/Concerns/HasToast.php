<?php

declare(strict_types=1);

namespace App\Http\Controllers\Concerns;

use Inertia\Inertia;

trait HasToast
{
    protected function toast(string $message, string $type = 'success'): void
    {
        Inertia::flash(compact('type', 'message'));
    }
}
