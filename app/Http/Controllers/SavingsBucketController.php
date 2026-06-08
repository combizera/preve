<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\CreateSavingsBucketRequest;
use App\Http\Requests\UpdateSavingsBucketRequest;
use App\Models\SavingsBucket;
use App\Services\SavingsHistoryService;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

final class SavingsBucketController extends Controller
{
    public function index(Request $request, SavingsHistoryService $history): Response
    {
        $this->authorize('viewAny', SavingsBucket::class);

        $user = Auth::user();
        $savingsBuckets = $user->savingsBuckets()->orderBy('id')->get();
        $availableYears = $history->availableYears($user);
        $selectedYear = $this->resolveYear($request, $availableYears);

        return Inertia::render('SavingsBucket', [
            'savingsBuckets'      => $savingsBuckets,
            'availableYears'      => $availableYears,
            'selectedYear'        => $selectedYear,
            'averageContribution' => $history->averageMonthlyContribution($user, 3),
            'chartData'           => Inertia::defer(fn (): array => $history->monthlyBalances($user, $selectedYear)),
            'categories'          => Inertia::defer(fn () => $user->categories()->get()),
        ]);
    }

    public function store(CreateSavingsBucketRequest $request): RedirectResponse
    {
        $this->authorize('create', SavingsBucket::class);

        $data = $request->validated();
        // initial_amount = pre-existing balance from outside the system, so no transaction is recorded.
        $data['current_amount'] = (int) ($data['initial_amount'] ?? 0);
        unset($data['initial_amount']);

        Auth::user()->savingsBuckets()->create($data);

        $this->toast::success(__('messages.savings_bucket.created'));

        return to_route('savings.index');
    }

    public function update(UpdateSavingsBucketRequest $request, SavingsBucket $savings): RedirectResponse
    {
        $this->authorize('update', $savings);

        $savings->update($request->validated());

        $this->toast::success(__('messages.savings_bucket.updated'));

        return to_route('savings.index');
    }

    public function destroy(SavingsBucket $savings): RedirectResponse
    {
        $this->authorize('delete', $savings);

        $savings->delete();

        $this->toast::success(__('messages.savings_bucket.deleted'));

        return to_route('savings.index');
    }

    /**
     * @param  list<int>  $availableYears
     */
    private function resolveYear(Request $request, array $availableYears): int
    {
        $current = CarbonImmutable::now()->year;
        $requested = (int) $request->integer('year', $current);

        return in_array($requested, $availableYears, true) ? $requested : $current;
    }
}
