<?php

declare(strict_types=1);

namespace App\Support;

final class DefaultTags
{
    public static function get(): array
    {
        return [
            [
                'name'        => 'Superfluous',
                'description' => 'Non-essential spending, wants and luxuries',
            ],
            [
                'name'        => 'Unexpected',
                'description' => 'Unplanned expenses, emergencies and surprises',
            ],
            [
                'name'        => 'Hobbies',
                'description' => 'Leisure activities and personal interests',
            ],
            [
                'name'        => 'Gift',
                'description' => 'Gifts given or received',
            ],
        ];
    }
}
