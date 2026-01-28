<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\CategoryColor;
use App\Enums\CategoryIcon;
use App\Enums\TransactionType;
use Database\Factories\CategoryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Category extends Model
{
    /** @use HasFactory<CategoryFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'type',
        'description',
        'color',
        'icon',
    ];

    protected $casts = [
        'type'  => TransactionType::class,
        'color' => CategoryColor::class,
        'icon'  => CategoryIcon::class,
    ];

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }
}
