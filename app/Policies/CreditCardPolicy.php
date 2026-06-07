<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\CreditCard;
use App\Models\User;

final class CreditCardPolicy
{
    public function viewAny(): bool
    {
        return true;
    }

    public function view(User $user, CreditCard $creditCard): bool
    {
        return $user->id === $creditCard->user_id;
    }

    public function create(): bool
    {
        return true;
    }

    public function update(User $user, CreditCard $creditCard): bool
    {
        return $user->id === $creditCard->user_id;
    }

    public function delete(User $user, CreditCard $creditCard): bool
    {
        return $user->id === $creditCard->user_id;
    }
}
