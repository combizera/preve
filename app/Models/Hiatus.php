<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonInterface;
use Database\Factories\HiatusFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property Carbon $started_at
 * @property Carbon|null $ended_at
 * @property string|null $note
 * @property Carbon|null $reconciled_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'started_at',
    'ended_at',
    'note',
    'reconciled_at',
])]
final class Hiatus extends Model
{
    /** @use HasFactory<HiatusFactory> */
    use HasFactory;

    protected $casts = [
        'started_at'    => 'date',
        'ended_at'      => 'date',
        'reconciled_at' => 'datetime',
    ];

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    #[Scope]
    protected function covering(Builder $query, CarbonInterface $date): Builder
    {
        return $query
            ->where('started_at', '<=', $date->toDateString())
            ->where(function (Builder $query) use ($date): void {
                $query
                    ->whereNull('ended_at')
                    ->orWhere('ended_at', '>=', $date->toDateString());
            });
    }

    #[Scope]
    protected function overlapping(Builder $query, CarbonInterface $start, ?CarbonInterface $end): Builder
    {
        return $query
            ->where(function (Builder $query) use ($start): void {
                $query
                    ->whereNull('ended_at')
                    ->orWhere('ended_at', '>=', $start->toDateString());
            })
            ->when($end instanceof CarbonInterface, fn (Builder $query): Builder => $query
                ->where('started_at', '<=', $end->toDateString()));
    }
}
