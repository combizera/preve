<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\ForecastService;
use App\Services\SavingsRateService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

final class DashboardController extends Controller
{
    public function __invoke(Request $request, ForecastService $forecastService, SavingsRateService $savingsRateService): Response
    {
        $user = Auth::user();
        $now = now();

        $latestTransactions = $user
            ->transactions()
            ->with('category')
            ->limit(5)
            ->orderBy('transaction_date', 'desc')
            ->get();

        $chartDate = $now->copy()
            ->setYear($request->integer('forecast_year', $now->year))
            ->setMonth($request->integer('forecast_month', $now->month))
            ->startOfMonth();

        $availableBalanceAsOf = $now->lessThan($chartDate->copy()->endOfMonth())
            ? $now
            : $chartDate->copy()->endOfMonth();

        $availableBalance = (int) $user->transactions()
            ->paid($availableBalanceAsOf)
            ->netBalance()
            ->value('net_balance');

        $forecast = $forecastService->calculate(
            $user,
            $now,
            $availableBalance,
            $request->integer('forecast_month', $now->month),
            $request->integer('forecast_year', $now->year),
        );

        $monthlyIncome = (int) $user->transactions()->inMonth($chartDate)->income()->sum('amount');
        $monthlyExpenses = (int) $user->transactions()->inMonth($chartDate)->expense()->sum('amount');

        $daysInMonth = $chartDate->endOfMonth()->day;

        $carryOver = (int) $user->transactions()->before($chartDate)->netBalance()->value('net_balance');

        $dailyNet = $user->transactions()->inMonth($chartDate)->dailyNet()->pluck('net', 'day');

        $dailyBalances = collect(range(1, $daysInMonth))->map(fn (int $day): array => [
            'day'    => $day,
            'amount' => (int) ($dailyNet[$day] ?? 0),
        ])->all();

        $dailyForecastedSpend = $forecastService->dailyForecastedSpend($user, $chartDate, $now);

        $categories = $user->categories()->get();
        $tags = $user->tags()->get();
        $savingsBuckets = $user->savingsBuckets()->orderBy('id')->get();
        $savingsRate = $savingsRateService->forMonth($user, $now);

        return Inertia::render('Dashboard', compact(
            'latestTransactions',
            'availableBalance',
            'forecast',
            'monthlyIncome',
            'monthlyExpenses',
            'dailyBalances',
            'dailyForecastedSpend',
            'carryOver',
            'categories',
            'tags',
            'savingsBuckets',
            'savingsRate',
        ));
    }
}
