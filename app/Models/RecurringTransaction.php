<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\FrequencyType;
use App\Enums\TransactionType;
use Database\Factories\RecurringTransactionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class RecurringTransaction extends Model
{
    /** @use HasFactory<RecurringTransactionFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'category_id',
        'tag_id',
        'amount',
        'frequency',
        'type',
        'description',
        'day_of_month',
        'start_date',
        'end_date',
    ];

    protected $casts = [
        'frequency'  => FrequencyType::class,
        'type'       => TransactionType::class,
        'start_date' => 'date',
        'end_date'   => 'date',
    ];

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function tag(): BelongsTo
    {
        return $this->belongsTo(Tag::class);
    }
}
