<?php

declare(strict_types=1);

namespace App\Filters;

use Carbon\Carbon;

final class TransactionFilter extends QueryFilter
{
    public function date_start(string $date): void
    {
        $this->builder->where('transaction_date', '>=', Carbon::parse($date)->startOfDay());
    }

    public function date_end(string $date): void
    {
        $this->builder->where('transaction_date', '<=', Carbon::parse($date)->endOfDay());
    }

    public function type(string $type): void
    {
        $this->builder->where('type', $type);
    }

    public function categories(array $categoryIds): void
    {
        $this->builder->whereHas('category', function ($query) use ($categoryIds) {
            $query->whereIn('categories.id', $categoryIds);
        });
    }

    public function tags(array $tagIds): void
    {
        $this->builder->whereHas('tag', function ($query) use ($tagIds) {
            $query->whereIn('tags.id', $tagIds);
        });
    }

    public function search(string $term): void
    {
        $this->builder->where(function ($query) use ($term) {
            $query->where('description', 'like', "%{$term}%")
                ->orWhereHas('category', fn ($q) => $q->where('name', 'like', "%{$term}%"));
        });
    }
}
