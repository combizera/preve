<?php

declare(strict_types=1);

namespace App\Enums;

enum CategoryColor: string
{
    case RED = 'red';
    case BLUE = 'blue';
    case GREEN = 'green';
    case YELLOW = 'yellow';
    case ORANGE = 'orange';
    case PURPLE = 'purple';

    public function label(): string
    {
        return match ($this) {
            self::RED    => 'red',
            self::BLUE   => 'blue',
            self::GREEN  => 'green',
            self::YELLOW => 'yellow',
            self::ORANGE => 'orange',
            self::PURPLE => 'purple',
        };
    }
}
