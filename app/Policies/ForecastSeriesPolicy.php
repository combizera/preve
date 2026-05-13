<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\ForecastSeries;
use App\Models\User;

final class ForecastSeriesPolicy
{
    public function viewAny(): bool
    {
        return false;
    }

    public function view(User $user, ForecastSeries $series): bool
    {
        return $user->id === $series->user_id;
    }

    public function create(): bool
    {
        return false;
    }

    public function update(User $user, ForecastSeries $series): bool
    {
        return $user->id === $series->user_id;
    }

    public function delete(User $user, ForecastSeries $series): bool
    {
        return $user->id === $series->user_id;
    }
}
