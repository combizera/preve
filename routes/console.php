<?php

declare(strict_types=1);

use App\Console\Commands\GenerateRecurringTransactions;
use App\Console\Commands\RolloverForecasts;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function (): void {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command(GenerateRecurringTransactions::class, ['--months=3'])->weekly();
Schedule::command(RolloverForecasts::class)->monthlyOn(1, '00:30');
