<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\TransactionType;
use App\Services\ForecastService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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

        $daysInMonth = $chartDate->copy()->endOfMonth()->day;

        $carryOver = (int) $user->transactions()
            ->where('transaction_date', '<', $chartDate->copy()->startOfMonth()->toDateString())
            ->selectRaw('COALESCE(SUM(CASE WHEN type = ? THEN amount ELSE -amount END), 0) as total', [TransactionType::INCOME->value])
            ->value('total');

        $dailyNet = $user->transactions()
            ->inMonth($chartDate)
            ->select(
                DB::raw('EXTRACT(DAY FROM transaction_date)::integer as day'),
                DB::raw('SUM(CASE WHEN type = \'' . TransactionType::INCOME->value . '\' THEN amount ELSE -amount END) as net'),
            )
            ->groupBy(DB::raw('EXTRACT(DAY FROM transaction_date)'))
            ->pluck('net', 'day');

        $dailyBalances = [];
        for ($d = 1; $d <= $daysInMonth; $d++) {
            $dailyBalances[] = [
                'day'    => $d,
                'amount' => (int) ($dailyNet[$d] ?? 0),
            ];
        }

        $categories = Auth::user()->categories()->get();
        $tags = Auth::user()->tags()->get();

        return Inertia::render('Dashboard', compact(
            'latestTransactions',
            'availableBalance',
            'forecast',
            'monthlyIncome',
            'monthlyExpenses',
            'dailyBalances',
            'carryOver',
            'categories',
            'tags',
        ));
    }
}
