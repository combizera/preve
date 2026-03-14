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
            $cursor->addMonth();
        }

        return $forecast;
    }

    private function monthBalance(User $user, CarbonInterface $date): int
    {
        $income = $user
            ->transactions()
            ->inMonth($date)
            ->income()
            ->sum('amount');

        $expenses = $user
            ->transactions()
            ->inMonth($date)
            ->expense()
            ->sum('amount');

        return $income - $expenses;
    }
}
