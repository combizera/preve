<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\SavingsBucket;
use App\Models\User;

final class SavingsBucketPolicy
{
    public function viewAny(): bool
    {
        return false;
    }

    public function view(User $user, SavingsBucket $savingsBucket): bool
    {
        return $user->id === $savingsBucket->user_id;
    }

    public function create(): bool
    {
        return true;
    }

    public function update(User $user, SavingsBucket $savingsBucket): bool
    {
        return $user->id === $savingsBucket->user_id;
    }

    public function delete(User $user, SavingsBucket $savingsBucket): bool
    {
        return $user->id === $savingsBucket->user_id;
    }
}
