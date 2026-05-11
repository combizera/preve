<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\TransactionType;
use App\Filters\QueryFilter;
use App\Observers\TransactionObserver;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * @property string $id
 * @property int $user_id
 * @property string|null $recurring_transaction_id
 * @property int|null $category_id
 * @property int|null $savings_bucket_id
 * @property int $amount
 * @property TransactionType $type
 * @property string $description
 * @property string|null $notes
 * @property Carbon $transaction_date
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[ObservedBy([TransactionObserver::class])]
#[Fillable([
    'user_id',
    'recurring_transaction_id',
    'category_id',
    'savings_bucket_id',
    'amount',
    'type',
    'description',
    'notes',
    'transaction_date',
])]
final class Transaction extends Model
{
    use HasFactory;
    use HasUuids;

    protected $casts = [
        'uuid'             => 'string',
        'transaction_date' => 'datetime',
        'type'             => TransactionType::class,
    ];

    public static function netAmountSql(): string
    {
        return 'SUM(CASE WHEN type = ? THEN amount ELSE -amount END)';
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<RecurringTransaction, $this>
     */
    public function recurringTransaction(): BelongsTo
    {
        return $this->belongsTo(RecurringTransaction::class);
    }

    /**
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * @return BelongsTo<SavingsBucket, $this>
     */
    public function savingsBucket(): BelongsTo
    {
        return $this->belongsTo(SavingsBucket::class);
    }

    /**
     * @return MorphToMany<Tag, $this>
     */
    public function tags(): MorphToMany
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }

    #[Scope]
    protected function filter(Builder $query, QueryFilter $filters): Builder
    {
        return $filters->apply($query);
    }

    #[Scope]
    protected function income(Builder $query): Builder
    {
        return $query->where('type', TransactionType::INCOME);
    }

    #[Scope]
    protected function expense(Builder $query): Builder
    {
        return $query->where('type', TransactionType::EXPENSE);
    }

    #[Scope]
    protected function inMonth(Builder $query, CarbonInterface $date): Builder
    {
        return $query->whereBetween('transaction_date', [
            $date->copy()->startOfMonth()->toDateString(),
            $date->copy()->endOfMonth()->toDateString(),
        ]);
    }

    #[Scope]
    protected function paid(Builder $query, CarbonInterface $date): Builder
    {
        return $query->where('transaction_date', '<=', $date->toDateString());
    }

    #[Scope]
    protected function pending(Builder $query, CarbonInterface $date): Builder
    {
        return $query->where('transaction_date', '>', $date->toDateString());
    }

    #[Scope]
    protected function before(Builder $query, CarbonInterface $date): Builder
    {
        return $query->where('transaction_date', '<', $date->copy()->startOfMonth()->toDateString());
    }

    #[Scope]
    protected function netBalance(Builder $query): Builder
    {
        return $query->selectRaw(
            'COALESCE(' . self::netAmountSql() . ', 0) as net_balance',
            [TransactionType::INCOME->value],
        );
    }

    #[Scope]
    protected function dailyNet(Builder $query): Builder
    {
        return $query
            ->selectRaw('EXTRACT(DAY FROM transaction_date)::integer as day')
            ->selectRaw(self::netAmountSql() . ' as net', [TransactionType::INCOME->value])
            ->groupBy(DB::raw('EXTRACT(DAY FROM transaction_date)'));
    }
}
