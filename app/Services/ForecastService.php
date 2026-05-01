<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Date;

final class ForecastService
{
    public function calculate(User $user, CarbonInterface $now, int $availableBalance, int $forecastMonth, int $forecastYear): int
    {
        $currentDate = Date::create($now->year, $now->month, 1);
        $selectedDate = Date::create($forecastYear, $forecastMonth, 1);

        if ($selectedDate->lessThan($currentDate)) {
            return $this->monthBalance($user, $selectedDate);
        }

        $pendingExpenses = $user->transactions()->inMonth($now)->pending($now)->expense()->sum('amount');
        $forecast = $availableBalance - $pendingExpenses;
        $forecast -= $this->forecastShortfall($user, $currentDate, $now);

        $cursor = $currentDate->copy()->addMonth();
        while ($cursor->lessThanOrEqualTo($selectedDate)) {
            $forecast += $this->monthBalance($user, $cursor);
            $forecast -= $this->forecastShortfall($user, $cursor, $now);
            $cursor = $cursor->addMonth();
        }

        return $forecast;
    }

    public function dailyForecastedSpend(User $user, CarbonInterface $chartDate, CarbonInterface $now): int
    {
        $isCurrentMonth = $chartDate->isSameMonth($now);
        $isFutureMonth = $chartDate->greaterThan($now->copy()->startOfMonth()) && !$isCurrentMonth;

        if (!$isCurrentMonth && !$isFutureMonth) {
            return 0;
        }

        $daysInMonth = $chartDate->copy()->endOfMonth()->day;
        $daysRemaining = $isCurrentMonth
            ? max(0, $daysInMonth - $now->day)
            : $daysInMonth;

        if ($daysRemaining === 0) {
            return 0;
        }

        $forecasts = $user->forecasts()
            ->whereYear('month', $chartDate->year)
            ->whereMonth('month', $chartDate->month)
            ->whereHas('series', fn ($query) => $query->where('is_active', true))
            ->get();

        return (int) $forecasts->sum(fn ($forecast): int => intdiv($forecast->remaining, $daysRemaining));
    }

    /**
     * Sum of expected forecast spend not yet captured in transactions for the
     * given month. Current month uses `remaining` (budget minus already-paid);
     * future months use the full `amount`. Past months return 0.
     */
    private function forecastShortfall(User $user, CarbonInterface $monthDate, CarbonInterface $now): int
    {
        $isCurrentMonth = $monthDate->isSameMonth($now);
        $isFutureMonth = $monthDate->greaterThan($now->copy()->startOfMonth()) && !$isCurrentMonth;

        if (!$isCurrentMonth && !$isFutureMonth) {
            return 0;
        }

        return (int) $user->forecasts()
            ->whereYear('month', $monthDate->year)
            ->whereMonth('month', $monthDate->month)
            ->whereHas('series', fn ($query) => $query->where('is_active', true))
            ->get()
            ->sum(fn ($forecast): int => $isCurrentMonth ? $forecast->remaining : $forecast->amount);
    }

    private function monthBalance(User $user, CarbonInterface $date): int
    {
        return (int) $user
            ->transactions()
            ->inMonth($date)
            ->netBalance()
            ->value('net_balance');
    }
}
