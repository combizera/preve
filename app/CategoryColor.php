<?php

namespace App;

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
            self::RED => 'Red',
            self::BLUE => 'Blue',
            self::GREEN => 'Green',
            self::YELLOW => 'Yellow',
            self::ORANGE => 'Orange',
            self::PURPLE => 'Purple',
        };
    }
}
