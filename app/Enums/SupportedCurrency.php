<?php

declare(strict_types=1);

namespace App\Enums;

enum SupportedCurrency: string
{
    case BRL = 'BRL';
    case USD = 'USD';

    public function symbol(): string
    {
        return match ($this) {
            SupportedCurrency::BRL => 'R$',
            SupportedCurrency::USD => '$',
        };
    }

    public function label(): string
    {
        return match ($this) {
            SupportedCurrency::BRL => 'Real (R$)',
            SupportedCurrency::USD => 'Dollar ($)',
        };
    }
}
