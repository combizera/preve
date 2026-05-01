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

        $cursor = $currentDate->copy()->addMonth();
        while ($cursor->lessThanOrEqualTo($selectedDate)) {
            $forecast += $this->monthBalance($user, $cursor);
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

    private function monthBalance(User $user, CarbonInterface $date): int
    {
        return (int) $user
            ->transactions()
            ->inMonth($date)
            ->netBalance()
            ->value('net_balance');
    }
}
