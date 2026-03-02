<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\User;
use App\Support\DefaultTags;

final class CreateDefaultTags
{
    public function execute(User $user): void
    {
        $defaultTags = DefaultTags::get();

        foreach ($defaultTags as $tag) {
            $user->tags()->create([
                'name'        => $tag['name'],
                'slug'        => str()->slug($tag['name']),
                'description' => $tag['description'],
            ]);
        }
    }
}
