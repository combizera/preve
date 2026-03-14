<?php

declare(strict_types=1);

namespace App\Observers;

use App\Actions\CreateDefaultCategories;
use App\Actions\CreateDefaultTags;
use App\Models\User;

final class UserObserver
{
    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        resolve(CreateDefaultCategories::class)->execute($user);
        resolve(CreateDefaultTags::class)->execute($user);
    }

    /**
     * Handle the User "updated" event.
     */
    public function updated(): void
    {
        //
    }

    /**
     * Handle the User "deleted" event.
     */
    public function deleted(): void
    {
        //
    }

    /**
     * Handle the User "restored" event.
     */
    public function restored(): void
    {
        //
    }

    /**
     * Handle the User "force deleted" event.
     */
    public function forceDeleted(): void
    {
        //
    }
}
