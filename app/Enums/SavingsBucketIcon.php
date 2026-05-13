<?php

declare(strict_types=1);

namespace App\Enums;

enum SavingsBucketIcon: string
{
    case SHIELD = 'shield';
    case PIGGY_BANK = 'piggy-bank';
    case PLANE = 'plane';
    case HOME = 'home';
    case CAR = 'car';
    case GRADUATION_CAP = 'graduation-cap';
    case BABY = 'baby';
    case HEART = 'heart';
    case STETHOSCOPE = 'stethoscope';
    case TRENDING_UP = 'trending-up';
    case GIFT = 'gift';
    case PAW_PRINT = 'paw-print';
    case LAPTOP = 'laptop';
    case HAMMER = 'hammer';
    case PARTY_POPPER = 'party-popper';
    case BRIEFCASE = 'briefcase';

    public function label(): string
    {
        return match ($this) {
            self::SHIELD         => 'Emergency fund',
            self::PIGGY_BANK     => 'Savings',
            self::PLANE          => 'Travel',
            self::HOME           => 'Home',
            self::CAR            => 'Car',
            self::GRADUATION_CAP => 'Education',
            self::BABY           => 'Kids',
            self::HEART          => 'Wedding',
            self::STETHOSCOPE    => 'Health',
            self::TRENDING_UP    => 'Investments',
            self::GIFT           => 'Gift',
            self::PAW_PRINT      => 'Pet',
            self::LAPTOP         => 'Electronics',
            self::HAMMER         => 'Renovation',
            self::PARTY_POPPER   => 'Party / event',
            self::BRIEFCASE      => 'Retirement',
        };
    }
}
