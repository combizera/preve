<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\AdjustBalanceRequest;
use App\Http\Requests\AdjustBucketsRequest;
use App\Http\Requests\ReviewRecurringRequest;
use App\Services\ReconciliationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

final class ReconciliationController extends Controller
{
    public function show(ReconciliationService $service): Response
    {
        return Inertia::render('Reconciliation', $service->overview(Auth::user()));
    }

    public function reviewRecurring(ReviewRecurringRequest $request, ReconciliationService $service): RedirectResponse
    {
        $service->reviewRecurring(
            Auth::user(),
            $request->validated('delete_transaction_ids') ?? [],
            $request->validated('deactivate_recurring_ids') ?? [],
        );

        $this->toast::success(__('messages.reconciliation.recurring_reviewed'));

        return to_route('reconciliation.show');
    }

    public function adjustBuckets(AdjustBucketsRequest $request, ReconciliationService $service): RedirectResponse
    {
        $service->adjustBuckets(Auth::user(), $request->validated('buckets') ?? []);

        $this->toast::success(__('messages.reconciliation.buckets_adjusted'));

        return to_route('reconciliation.show');
    }

    public function adjustBalance(AdjustBalanceRequest $request, ReconciliationService $service): RedirectResponse
    {
        $service->adjustBalance(Auth::user(), (int) $request->validated('real_balance'));

        $this->toast::success(__('messages.reconciliation.balance_adjusted'));

        return to_route('reconciliation.show');
    }

    public function complete(ReconciliationService $service): RedirectResponse
    {
        $service->complete(Auth::user());

        $this->toast::success(__('messages.reconciliation.completed'));

        return to_route('dashboard');
    }
}
