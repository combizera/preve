<?php

namespace App\Models;

use App\Enums\CategoryColor;
use App\Enums\CategoryIcon;
use Database\Factories\CategoryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    /** @use HasFactory<CategoryFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'color',
        'icon',
    ];

    protected $casts = [
        'color' => CategoryColor::class,
        'icon' => CategoryIcon::class,
    ];

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }
}
