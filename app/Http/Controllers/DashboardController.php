<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\ForecastService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

final class DashboardController extends Controller
{
    public function __invoke(Request $request, ForecastService $forecastService): Response
    {
        $user = Auth::user();
        $now = now();

        $latestTransactions = $user
            ->transactions()
            ->with('category')
            ->limit(5)
            ->orderBy('transaction_date', 'desc')
            ->get();

        $totalIncome = $user->transactions()->inMonth($now)->paid($now)->income()->sum('amount');
        $totalExpenses = $user->transactions()->inMonth($now)->paid($now)->expense()->sum('amount');
        $availableBalance = $totalIncome - $totalExpenses;

        $forecast = $forecastService->calculate(
            $user,
            $now,
            $availableBalance,
            $request->integer('forecast_month', $now->month),
            $request->integer('forecast_year', $now->year),
        );

        $chartDate = $now->copy()->setYear(
            $request->integer('forecast_year', $now->year)
        )->setMonth(
            $request->integer('forecast_month', $now->month)
        );

        $monthlyIncome = (int) $user->transactions()->inMonth($chartDate)->income()->sum('amount');
        $monthlyExpenses = (int) $user->transactions()->inMonth($chartDate)->expense()->sum('amount');

        $categories = Auth::user()->categories()->get();
        $tags = Auth::user()->tags()->get();

        return Inertia::render('Dashboard', compact(
            'latestTransactions',
            'availableBalance',
            'forecast',
            'monthlyIncome',
            'monthlyExpenses',
            'categories',
            'tags',
        ));
    }
}
