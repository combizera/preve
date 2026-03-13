<?php

declare(strict_types=1);

namespace App\Filters;

use Illuminate\Support\Facades\Date;

final class TransactionFilter extends QueryFilter
{
    public function date_start(string $date): void
    {
        $this->builder->where('transaction_date', '>=', Date::parse($date)->startOfDay());
    }

    public function date_end(string $date): void
    {
        $this->builder->where('transaction_date', '<=', Date::parse($date)->endOfDay());
    }

    public function type(string $type): void
    {
        $this->builder->where('type', $type);
    }

    public function categories(array $categoryIds): void
    {
        $this->builder->whereHas('category', function ($query) use ($categoryIds): void {
            $query->whereIn('categories.id', $categoryIds);
        });
    }

    public function tags(array $tagIds): void
    {
        $this->builder->whereHas('tag', function ($query) use ($tagIds): void {
            $query->whereIn('tags.id', $tagIds);
        });
    }

    public function search(string $term): void
    {
        $lowerTerm = sprintf('%%%s%%', mb_strtolower($term));

        $this->builder->where(function ($query) use ($lowerTerm): void {
            $query->whereRaw('LOWER(description) LIKE ?', [$lowerTerm])
                ->orWhereHas('category', function ($q) use ($lowerTerm): void {
                    $q->whereRaw('LOWER(name) LIKE ?', [$lowerTerm]);
                });
        });
    }
}
