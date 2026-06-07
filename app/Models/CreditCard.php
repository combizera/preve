<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\AccentColor;
use Carbon\CarbonInterface;
use Database\Factories\CreditCardFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property string $name
 * @property string|null $last_four
 * @property int $closing_day
 * @property int $due_day
 * @property AccentColor $color
 * @property int|null $credit_limit
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'name',
    'last_four',
    'closing_day',
    'due_day',
    'color',
    'credit_limit',
])]
final class CreditCard extends Model
{
    /** @use HasFactory<CreditCardFactory> */
    use HasFactory;

    protected $casts = [
        'closing_day'  => 'integer',
        'due_day'      => 'integer',
        'credit_limit' => 'integer',
        'color'        => AccentColor::class,
    ];

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

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
     * The day cash actually leaves the account for a purchase made on the given
     * date: the due day of the invoice the purchase closes into. Purchases after
     * the closing day roll to the next invoice; a due day not after the closing
     * day falls in the month following the close.
     */
    public function effectivePaymentDate(CarbonInterface $purchaseDate): CarbonInterface
    {
        $monthsUntilClose = $purchaseDate->day <= $this->closing_day ? 0 : 1;
        $monthsCloseToDue = $this->due_day > $this->closing_day ? 0 : 1;

        $dueMonth = $purchaseDate->copy()
            ->startOfMonth()
            ->addMonthsNoOverflow($monthsUntilClose + $monthsCloseToDue);

        return $dueMonth
            ->setDay(min($this->due_day, $dueMonth->daysInMonth))
            ->startOfDay();
    }
}
