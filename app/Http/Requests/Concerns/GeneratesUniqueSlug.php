<?php

declare(strict_types=1);

namespace App\Http\Requests\Concerns;

use Illuminate\Support\Str;

trait GeneratesUniqueSlug
{
    /**
     * Get the model class for slug uniqueness check.
     */
    abstract protected function getModelClass(): string;

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        if (!$this->has('name') || !$this->input('name')) {
            return;
        }

        $this->merge([
            'slug' => $this->generateUniqueSlug($this->input('name')),
        ]);
    }

    /**
     * Generate a unique slug based on the name.
     */
    protected function generateUniqueSlug(string $name): string
    {
        $slug = Str::slug($name);
        $userId = $this->user()->id;
        $modelClass = $this->getModelClass();

        $existingSlugs = $modelClass::query()
            ->where('user_id', $userId)
            ->where(function ($query) use ($slug) {
                $query->where('slug', $slug)
                    ->orWhere('slug', 'LIKE', "{$slug}-%");
            })
            ->pluck('slug');

        if (!$existingSlugs->contains($slug)) {
            return $slug;
        }

        $count = 1;
        while ($existingSlugs->contains("{$slug}-{$count}")) {
            $count++;
        }

        return "{$slug}-{$count}";
    }
}
