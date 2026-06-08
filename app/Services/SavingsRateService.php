<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use Carbon\CarbonInterface;

final class SavingsRateService
{
    /**
     * @return array{deposits: int, income: int, rate: float|null}
     */
    public function forMonth(User $user, CarbonInterface $date): array
    {
        $deposits = (int) $user
            ->transactions()
            ->expense()
            ->inMonth($date)
            ->whereNotNull('savings_bucket_id')
            ->sum('amount');

        $income = (int) $user
            ->transactions()
            ->income()
            ->inMonth($date)
            ->whereNull('savings_bucket_id')
            ->sum('amount');

        return [
            'deposits' => $deposits,
            'income'   => $income,
            'rate'     => $income > 0 ? round($deposits / $income * 100, 2) : null,
        ];
    }
}
