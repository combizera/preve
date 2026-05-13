<?php

declare(strict_types=1);

namespace App\Enums;

enum ForecastPaceStatus: string
{
    case OVER_PACE = 'over_pace';
    case ON_PACE = 'on_pace';
    case UNDER_PACE = 'under_pace';

    public function label(): string
    {
        return match ($this) {
            ForecastPaceStatus::OVER_PACE  => 'Over pace',
            ForecastPaceStatus::ON_PACE    => 'On pace',
            ForecastPaceStatus::UNDER_PACE => 'Under pace',
        };
    }
}
