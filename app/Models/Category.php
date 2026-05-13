<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\AccentColor;
use App\Enums\CategoryIcon;
use App\Enums\TransactionType;
use Database\Factories\CategoryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

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
    'order',
])]
final class Category extends Model
{
    /** @use HasFactory<CategoryFactory> */
    use HasFactory;

    protected $casts = [
        'type'  => TransactionType::class,
        'color' => AccentColor::class,
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
     * The unique constraint on `(user_id, category_id)` in `forecast_series`
     * guarantees a category has at most one series.
     *
     * @return HasOne<ForecastSeries, $this>
     */
    public function forecastSeries(): HasOne
    {
        return $this->hasOne(ForecastSeries::class);
    }

    protected static function booted(): void
    {
        self::creating(function (Category $category): void {
            if ($category->order !== null) {
                return;
            }

            $type = $category->type instanceof TransactionType
                ? $category->type->value
                : $category->type;

            $maxOrder = self::query()
                ->where('user_id', $category->user_id)
                ->where('type', $type)
                ->max('order');

            $category->order = ($maxOrder ?? 0) + 1;
        });
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
