<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ForecastPaceStatus;
use App\Enums\TransactionType;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Database\Factories\ForecastFactory;
use Illuminate\Database\Eloquent\Attributes\Appends;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Date;

/**
 * @property string $id
 * @property string $forecast_series_id
 * @property int $user_id
 * @property int $category_id
 * @property int $amount
 * @property CarbonImmutable $month
 * @property string|null $notes
 * @property-read ForecastSeries $series
 * @property-read Category $category
 * @property-read User $user
 * @property-read bool $is_active
 * @property-read int $daily_allowance
 * @property-read int $spent_to_date
 * @property-read int $remaining
 * @property-read string $pace_status
 */
#[Fillable([
    'forecast_series_id',
    'user_id',
    'category_id',
    'amount',
    'month',
    'notes',
])]
#[Appends([
    'is_active',
    'daily_allowance',
    'spent_to_date',
    'remaining',
    'pace_status',
])]
#[Hidden([
    'series',
])]
final class Forecast extends Model
{
    /** @use HasFactory<ForecastFactory> */
    use HasFactory;

    use HasUuids;

    protected $casts = [
        'month' => 'date:Y-m-d',
    ];

    private ?int $cachedSpentToDate = null;

    private ?ForecastPaceStatus $cachedPaceStatus = null;

    /**
     * @return BelongsTo<ForecastSeries, $this>
     */
    public function series(): BelongsTo
    {
        return $this->belongsTo(ForecastSeries::class, 'forecast_series_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Linear daily share of the budget (cents). Uses integer division so callers
     * always work in whole-cent units; remainder bias is acceptable for budgets.
     */
    public function computeDailyAllowance(): int
    {
        return intdiv($this->amount, $this->month->daysInMonth);
    }

    /**
     * Sum of paid expense transactions in this forecast's category and month,
     * up to and including $asOf (default today).
     */
    public function computeSpentToDate(?CarbonInterface $asOf = null): int
    {
        $asOf ??= Date::today();

        return (int) Transaction::query()
            ->where('user_id', $this->user_id)
            ->where('category_id', $this->category_id)
            ->where('type', TransactionType::EXPENSE)
            ->whereBetween('transaction_date', [
                $this->month->copy()->startOfMonth()->toDateString(),
                $this->month->copy()->endOfMonth()->toDateString(),
            ])
            ->where('transaction_date', '<=', $asOf->toDateString())
            ->sum('amount');
    }

    public function computeRemaining(?CarbonInterface $asOf = null): int
    {
        return $this->amount - $this->computeSpentToDate($asOf);
    }

    /**
     * Linear pace check. Compares actual spent against the share of the budget
     * that should have been used by $asOf, given a flat distribution across the
     * month. Past months clamp $asOf to month end; future months return ON_PACE.
     */
    public function computePaceStatus(?CarbonInterface $asOf = null, ?int $spentToDate = null): ForecastPaceStatus
    {
        $asOf ??= Date::today();

        $monthStart = $this->month->copy()->startOfMonth();
        $monthEnd = $this->month->copy()->endOfMonth();

        if ($asOf->isBefore($monthStart)) {
            return ForecastPaceStatus::ON_PACE;
        }

        $effectiveAsOf = $asOf->isAfter($monthEnd) ? $monthEnd : $asOf;
        $daysPassed = $effectiveAsOf->day;
        $daysInMonth = $this->month->daysInMonth;

        $expectedByNow = ($this->amount * $daysPassed) / $daysInMonth;

        if ($expectedByNow <= 0) {
            return ForecastPaceStatus::ON_PACE;
        }

        $spent = $spentToDate ?? $this->computeSpentToDate($effectiveAsOf);
        $ratio = $spent / $expectedByNow;

        return match (true) {
            $ratio > 1.05 => ForecastPaceStatus::OVER_PACE,
            $ratio < 0.85 => ForecastPaceStatus::UNDER_PACE,
            default       => ForecastPaceStatus::ON_PACE,
        };
    }

    protected function getIsActiveAttribute(): bool
    {
        return $this->series->is_active;
    }

    protected function getDailyAllowanceAttribute(): int
    {
        return $this->computeDailyAllowance();
    }

    protected function getSpentToDateAttribute(): int
    {
        return $this->cachedSpentToDate ??= $this->computeSpentToDate();
    }

    protected function getRemainingAttribute(): int
    {
        return $this->amount - $this->getSpentToDateAttribute();
    }

    protected function getPaceStatusAttribute(): string
    {
        return ($this->cachedPaceStatus ??= $this->computePaceStatus(spentToDate: $this->getSpentToDateAttribute()))->value;
    }
}
