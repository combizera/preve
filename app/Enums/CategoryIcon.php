<?php

declare(strict_types=1);

namespace App\Enums;

enum CategoryIcon: string
{
    case HOUSE = 'house';
    case SHOPPING_CART = 'shopping-cart';
    case CAR = 'car';
    case HEART = 'heart';
    case COFFEE = 'coffee';
    case UTENSILS = 'utensils';
    case PLANE = 'plane';
    case BRIEFCASE = 'briefcase';
    case GAMEPAD_2 = 'gamepad-2';
    case MUSIC = 'music';
    case BOOK = 'book';
    case DUMBBELL = 'dumbbell';
    case SHOPPING_BAG = 'shopping-bag';
    case FILM = 'film';
    case LAPTOP = 'laptop';

    public function label(): string
    {
        return match ($this) {
            self::HOUSE         => 'House',
            self::SHOPPING_CART => 'Shopping Cart',
            self::CAR           => 'Car',
            self::HEART         => 'Heart',
            self::COFFEE        => 'Coffee',
            self::UTENSILS      => 'Utensils',
            self::PLANE         => 'Plane',
            self::BRIEFCASE     => 'Briefcase',
            self::GAMEPAD_2     => 'Game Controller',
            self::MUSIC         => 'Music',
            self::BOOK          => 'Book',
            self::DUMBBELL      => 'Fitness',
            self::SHOPPING_BAG  => 'Shopping Bag',
            self::FILM          => 'Film',
            self::LAPTOP        => 'Laptop',
        };
    }
}
