<?php

declare(strict_types=1);

namespace App\Enums;

enum FrequencyType: string
{
    case MONTHLY = 'monthly';
    case YEARLY = 'yearly';

    public function label(): string
    {
        return match ($this) {
            FrequencyType::MONTHLY => 'Monthly',
            FrequencyType::YEARLY  => 'Yearly',
        };
    }
}
