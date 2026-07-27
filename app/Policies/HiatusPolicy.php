<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Hiatus;
use App\Models\User;

final class HiatusPolicy
{
    public function viewAny(): bool
    {
        return true;
    }

    public function view(User $user, Hiatus $hiatus): bool
    {
        return $user->id === $hiatus->user_id;
    }

    public function create(): bool
    {
        return true;
    }

    public function update(User $user, Hiatus $hiatus): bool
    {
        return $user->id === $hiatus->user_id;
    }

    public function delete(User $user, Hiatus $hiatus): bool
    {
        return $user->id === $hiatus->user_id;
    }
}
