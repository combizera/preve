<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\TransactionType;
use App\Http\Requests\CreateForecastRequest;
use App\Http\Requests\UpdateForecastRequest;
use App\Models\Forecast;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

final class ForecastController extends Controller
{
    public function index(): Response
    {
        $forecasts = Auth::user()
            ->forecasts()
            ->with(['category', 'series'])
            ->orderBy('month', 'desc')
            ->get();

        $categories = Auth::user()->categories()
            ->where('type', TransactionType::EXPENSE)
            ->availableForForecast()
            ->get();

        return Inertia::render('Forecast', compact('forecasts', 'categories'));
    }

    /**
     * @throws Throwable
     */
    public function store(CreateForecastRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $month = Date::parse($validated['month'])->startOfMonth()->toDateString();

        DB::transaction(function () use ($validated, $month): void {
            $series = Auth::user()->forecastSeries()->create([
                'category_id'    => $validated['category_id'],
                'default_amount' => $validated['amount'],
                'default_notes'  => $validated['notes'] ?? null,
                'is_active'      => true,
            ]);

            $series->forecasts()->create([
                'user_id'     => Auth::id(),
                'category_id' => $validated['category_id'],
                'amount'      => $validated['amount'],
                'month'       => $month,
                'notes'       => $validated['notes'] ?? null,
            ]);
        });

        $this->toast::success(__('messages.forecast.created'));

        return to_route('forecasts.index');
    }

    /**
     * @throws Throwable
     */
    public function update(UpdateForecastRequest $request, Forecast $forecast): RedirectResponse
    {
        $this->authorize('update', $forecast);

        $validated = $request->validated();
        $applyToDefault = (bool) ($validated['apply_to_default'] ?? false);

        DB::transaction(function () use ($forecast, $validated, $applyToDefault): void {
            $forecast->update([
                'amount' => $validated['amount'],
                'notes'  => $validated['notes'] ?? null,
            ]);

            if ($applyToDefault) {
                $forecast->series->update([
                    'default_amount' => $validated['amount'],
                    'default_notes'  => $validated['notes'] ?? null,
                ]);
            }
        });

        $this->toast::success(__('messages.forecast.updated'));

        return to_route('forecasts.index');
    }

    public function toggle(Forecast $forecast): RedirectResponse
    {
        $this->authorize('update', $forecast);

        $series = $forecast->series;
        $series->update(['is_active' => !$series->is_active]);

        if ($series->is_active) {
            $this->toast::success(__('messages.forecast.activated'));
        } else {
            $this->toast::warning(__('messages.forecast.paused'));
        }

        return to_route('forecasts.index');
    }

    /**
     * @throws Throwable
     */
    public function destroy(Forecast $forecast): RedirectResponse
    {
        $this->authorize('delete', $forecast);

        DB::transaction(function () use ($forecast): void {
            // Pause + delete = full removal of the series and all its instances.
            if (!$forecast->series->is_active) {
                $forecast->series->delete();

                return;
            }

            $forecast->delete();
        });

        $this->toast::success(__('messages.forecast.deleted'));

        return to_route('forecasts.index');
    }
}
