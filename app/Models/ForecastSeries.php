<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\ForecastSeriesFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string $id
 * @property int $user_id
 * @property int $category_id
 * @property int $default_amount
 * @property string|null $default_notes
 * @property bool $is_active
 * @property-read User $user
 * @property-read Category $category
 * @property-read Collection<int, Forecast> $forecasts
 */
#[Fillable([
    'user_id',
    'category_id',
    'default_amount',
    'default_notes',
    'is_active',
])]
#[Table(name: 'forecast_series')]
final class ForecastSeries extends Model
{
    /** @use HasFactory<ForecastSeriesFactory> */
    use HasFactory;

    use HasUuids;

    protected $casts = [
        'is_active' => 'boolean',
    ];

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
     * @return HasMany<Forecast, $this>
     */
    public function forecasts(): HasMany
    {
        return $this->hasMany(Forecast::class);
    }
}
