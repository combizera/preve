<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\TransactionType;
use App\Filters\QueryFilter;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

final class Transaction extends Model
{
    use HasFactory;
    use HasUuids;

    protected $fillable = [
        'user_id',
        'recurring_transaction_id',
        'category_id',
        'tag_id',
        'amount',
        'type',
        'description',
        'notes',
        'transaction_date',
    ];

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
     * @return BelongsTo<Tag, $this>
     */
    public function tag(): BelongsTo
    {
        return $this->belongsTo(Tag::class);
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

    public function scopeBefore(Builder $query, CarbonInterface $date): Builder
    {
        return $query->where('transaction_date', '<', $date->copy()->startOfMonth()->toDateString());
    }

    public function scopeNetBalance(Builder $query): Builder
    {
        return $query->selectRaw(
            'COALESCE(' . self::netAmountSql() . ', 0) as net_balance',
            [TransactionType::INCOME->value],
        );
    }

    public function scopeDailyNet(Builder $query): Builder
    {
        return $query
            ->selectRaw('EXTRACT(DAY FROM transaction_date)::integer as day')
            ->selectRaw(self::netAmountSql() . ' as net', [TransactionType::INCOME->value])
            ->groupBy(DB::raw('EXTRACT(DAY FROM transaction_date)'));
    }
}
