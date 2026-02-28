<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\TransactionType;
use App\Filters\QueryFilter;
use Carbon\CarbonInterface;
use Database\Factories\TransactionFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class Transaction extends Model
{
    /** @use HasFactory<TransactionFactory> */
    use HasFactory, HasUuids;

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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function recurringTransaction(): BelongsTo
    {
        return $this->belongsTo(RecurringTransaction::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function tag(): BelongsTo
    {
        return $this->belongsTo(Tag::class);
    }

    public function scopeFilter(Builder $query, QueryFilter $filters): Builder
    {
        return $filters->apply($query);
    }

    public function scopeIncome(Builder $query): Builder
    {
        return $query->where('type', TransactionType::INCOME);
    }

    public function scopeExpense(Builder $query): Builder
    {
        return $query->where('type', TransactionType::EXPENSE);
    }

    public function scopeInMonth(Builder $query, CarbonInterface $date): Builder
    {
        return $query->whereBetween('transaction_date', [
            $date->copy()->startOfMonth()->toDateString(),
            $date->copy()->endOfMonth()->toDateString(),
        ]);
    }

    public function scopePaid(Builder $query, CarbonInterface $date): Builder
    {
        return $query->where('transaction_date', '<=', $date->toDateString());
    }

    public function scopePending(Builder $query, CarbonInterface $date): Builder
    {
        return $query->where('transaction_date', '>', $date->toDateString());
    }
}
