<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\AccentColor;
use App\Enums\SavingsBucketIcon;
use Database\Factories\SavingsBucketFactory;
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
 * @property int $target_amount
 * @property int $current_amount
 * @property AccentColor $color
 * @property SavingsBucketIcon $icon
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'name',
    'target_amount',
    'current_amount',
    'color',
    'icon',
])]
final class SavingsBucket extends Model
{
    /** @use HasFactory<SavingsBucketFactory> */
    use HasFactory;

    protected $casts = [
        'target_amount'  => 'integer',
        'current_amount' => 'integer',
        'color'          => AccentColor::class,
        'icon'           => SavingsBucketIcon::class,
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
}
