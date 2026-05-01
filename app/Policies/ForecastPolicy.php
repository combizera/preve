<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Forecast;
use App\Models\User;

final class ForecastPolicy
{
    public function viewAny(): bool
    {
        return false;
    }

    public function view(User $user, Forecast $forecast): bool
    {
        return $user->id === $forecast->user_id;
    }

    public function create(): bool
    {
        return false;
    }

    public function update(User $user, Forecast $forecast): bool
    {
        return $user->id === $forecast->user_id;
    }

    public function delete(User $user, Forecast $forecast): bool
    {
        return $user->id === $forecast->user_id;
    }
}
