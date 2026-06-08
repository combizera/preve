<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\RecurringTransaction;
use App\Models\User;

final class RecurringTransactionPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(): bool
    {
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, RecurringTransaction $recurringTransaction): bool
    {
        return $user->id === $recurringTransaction->user_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, RecurringTransaction $recurringTransaction): bool
    {
        return $user->id === $recurringTransaction->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, RecurringTransaction $recurringTransaction): bool
    {
        return $user->id === $recurringTransaction->user_id;
    }
}
