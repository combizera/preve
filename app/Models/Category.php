<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\CategoryColor;
use App\Enums\CategoryIcon;
use App\Enums\TransactionType;
use Database\Factories\CategoryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @method static Builder<Category> availableForForecast()
 */
#[Fillable([
    'name',
    'slug',
    'type',
    'description',
    'color',
    'icon',
])]
final class Category extends Model
{
    /** @use HasFactory<CategoryFactory> */
    use HasFactory;

    protected $casts = [
        'type'  => TransactionType::class,
        'color' => CategoryColor::class,
        'icon'  => CategoryIcon::class,
    ];

    /**
     * @return HasMany<Transaction, $this>
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * @return HasMany<RecurringTransaction, $this>
     */
    public function recurringTransactions(): HasMany
    {
        return $this->hasMany(RecurringTransaction::class);
    }

    /**
     * @return HasMany<Forecast, $this>
     */
    public function forecasts(): HasMany
    {
        return $this->hasMany(Forecast::class);
    }

    /**
     * @return HasMany<ForecastSeries, $this>
     */
    public function forecastSeries(): HasMany
    {
        return $this->hasMany(ForecastSeries::class);
    }

    /**
     * Categories that don't yet have a forecast series — eligible to be picked
     * when creating a new forecast.
     *
     * @param  Builder<Category>  $query
     */
    protected function scopeAvailableForForecast(Builder $query): void
    {
        $query->whereDoesntHave('forecastSeries');
    }
}
