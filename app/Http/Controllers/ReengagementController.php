<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\InactivityService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

final class ReengagementController extends Controller
{
    public function dismiss(InactivityService $inactivity): RedirectResponse
    {
        $inactivity->dismissBanner(Auth::user());

        return back();
    }
}
