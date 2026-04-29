<?php

declare(strict_types=1);

namespace App\Models;

use App\Eloquent\Relations\MorphToManyOnVarcharId;
use App\Enums\FrequencyType;
use App\Enums\TransactionType;
use Carbon\CarbonInterface;
use Database\Factories\RecurringTransactionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Facades\Date;

#[Fillable([
    'user_id',
    'category_id',
    'amount',
    'frequency',
    'type',
    'description',
    'is_active',
    'day_of_month',
    'start_date',
    'end_date',
])]
final class RecurringTransaction extends Model
{
    /** @use HasFactory<RecurringTransactionFactory> */
    use HasFactory;

    protected $casts = [
        'frequency'  => FrequencyType::class,
        'type'       => TransactionType::class,
        'is_active'  => 'boolean',
        'start_date' => 'date',
        'end_date'   => 'date',
    ];

    /**
     * @return HasMany<Transaction, $this>
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
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
     * @return MorphToMany<Tag, $this>
     */
    public function tags(): MorphToMany
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }

    public function calculateExactDateForMonth(CarbonInterface $month): CarbonInterface
    {
        if ($this->frequency === FrequencyType::YEARLY) {
            $targetMonth = $this->start_date->month;
            $reference = Date::createFromDate($month->year, $targetMonth, 1);
            $day = min($this->day_of_month, $reference->daysInMonth);

            return Date::createFromDate($month->year, $targetMonth, $day)->startOfDay();
        }

        $day = min($this->day_of_month, $month->daysInMonth);

        return Date::createFromDate($month->year, $month->month, $day)->startOfDay();
    }

    public function isValidTransactionDate(CarbonInterface $date): bool
    {
        if ($date->isBefore($this->start_date)) {
            return false;
        }

        return !($this->end_date && $date->isAfter($this->end_date));
    }

    /**
     * Use the varchar-aware MorphToMany so eager-loaded morph relations
     * (e.g. `tags`) bind this model's integer key as a string against the
     * polymorphic `taggables.taggable_id` varchar column, which also
     * accommodates UUID-keyed siblings like Transaction.
     */
    protected function newMorphToMany(
        Builder $query,
        Model $parent,
        $name,
        $table,
        $foreignPivotKey,
        $relatedPivotKey,
        $parentKey,
        $relatedKey,
        $relationName = null,
        $inverse = false,
    ): MorphToManyOnVarcharId {
        return new MorphToManyOnVarcharId(
            $query,
            $parent,
            $name,
            $table,
            $foreignPivotKey,
            $relatedPivotKey,
            $parentKey,
            $relatedKey,
            $relationName,
            $inverse,
        );
    }
}
