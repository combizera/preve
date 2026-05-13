<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\ForecastSeries;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Log;
use Throwable;

final class RolloverForecasts extends Command
{
    /**
     * @var string
     */
    protected $signature = 'forecasts:rollover';

    /**
     * @var string
     */
    protected $description = 'Creates a forecast for the current month for every active forecast series, snapshotting the series defaults.';

    public function handle(): void
    {
        $currentMonth = Date::now()->startOfMonth()->toDateString();
        $created = 0;
        $failed = 0;

        ForecastSeries::query()
            ->where('is_active', true)
            ->chunkById(200, function (Collection $seriesChunk) use ($currentMonth, &$created, &$failed): void {
                foreach ($seriesChunk as $series) {
                    try {
                        $forecast = $series->forecasts()->firstOrCreate(
                            ['month' => $currentMonth],
                            [
                                'user_id'     => $series->user_id,
                                'category_id' => $series->category_id,
                                'amount'      => $series->default_amount,
                                'notes'       => $series->default_notes,
                            ],
                        );

                        if ($forecast->wasRecentlyCreated) {
                            $created++;
                        }
                    } catch (Throwable $e) {
                        $failed++;
                        Log::error('Forecast rollover failed for series', [
                            'series_id' => $series->id,
                            'error'     => $e->getMessage(),
                        ]);
                    }
                }
            });

        $this->info("Rollover complete for {$currentMonth}. Created: {$created}. Failed: {$failed}.");
    }
}
