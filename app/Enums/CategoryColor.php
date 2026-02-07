<?php

declare(strict_types=1);

namespace App\Enums;

enum CategoryColor: string
{
    case RED = 'red';
    case ORANGE = 'orange';
    case AMBER = 'amber';
    case YELLOW = 'yellow';
    case LIME = 'lime';
    case GREEN = 'green';
    case EMERALD = 'emerald';
    case TEAL = 'teal';
    case CYAN = 'cyan';
    case SKY = 'sky';
    case BLUE = 'blue';
    case INDIGO = 'indigo';
    case VIOLET = 'violet';
    case PURPLE = 'purple';
    case FUCHSIA = 'fuchsia';
    case PINK = 'pink';

    public function label(): string
    {
        return match ($this) {
            self::RED     => 'red',
            self::ORANGE  => 'orange',
            self::AMBER   => 'amber',
            self::YELLOW  => 'yellow',
            self::LIME    => 'lime',
            self::GREEN   => 'green',
            self::EMERALD => 'emerald',
            self::TEAL    => 'teal',
            self::CYAN    => 'cyan',
            self::SKY     => 'sky',
            self::BLUE    => 'blue',
            self::INDIGO  => 'indigo',
            self::VIOLET  => 'violet',
            self::PURPLE  => 'purple',
            self::FUCHSIA => 'fuchsia',
            self::PINK    => 'pink',
        };
    }
}
