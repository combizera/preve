<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\HasToast;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

abstract class Controller
{
    use AuthorizesRequests;
    use HasToast;
}
