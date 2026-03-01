<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonInterface;

final class ForecastService
{
    public function calculate(User $user, CarbonInterface $now, int $availableBalance, int $forecastMonth, int $forecastYear): int
    {
        $currentDate = Carbon::create($now->year, $now->month, 1);
        $selectedDate = Carbon::create($forecastYear, $forecastMonth, 1);

        if ($selectedDate->equalTo($currentDate)) {
            $pendingExpenses = $user->transactions()->inMonth($now)->pending($now)->expense()->sum('amount');

            return $availableBalance - $pendingExpenses;
        }

        if ($selectedDate->greaterThan($currentDate)) {
            $pendingExpenses = $user->transactions()->inMonth($now)->pending($now)->expense()->sum('amount');
            $forecast = $availableBalance - $pendingExpenses;

            $cursor = $currentDate->copy()->addMonth();
            while ($cursor->lessThanOrEqualTo($selectedDate)) {
                $monthIncome = $user->transactions()->inMonth($cursor)->income()->sum('amount');
                $monthExpenses = $user->transactions()->inMonth($cursor)->expense()->sum('amount');
                $forecast += $monthIncome - $monthExpenses;
                $cursor->addMonth();
            }

            return $forecast;
        }

        // Past month: income - expenses for that month
        $income = $user->transactions()->inMonth($selectedDate)->income()->sum('amount');
        $expenses = $user->transactions()->inMonth($selectedDate)->expense()->sum('amount');

        return $income - $expenses;
    }
}
