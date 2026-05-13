<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\TagFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

#[Fillable([
    'name',
    'slug',
    'description',
])]
final class Tag extends Model
{
    /** @use HasFactory<TagFactory> */
    use HasFactory;

    /**
     * @return MorphToMany<Transaction, $this>
     */
    public function transactions(): MorphToMany
    {
        return $this->morphedByMany(Transaction::class, 'taggable');
    }

    /**
     * @return MorphToMany<RecurringTransaction, $this>
     */
    public function recurringTransactions(): MorphToMany
    {
        return $this->morphedByMany(RecurringTransaction::class, 'taggable');
    }
}
