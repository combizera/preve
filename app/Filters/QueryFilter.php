<?php

declare(strict_types=1);

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

abstract class QueryFilter
{
    protected Builder $builder;

    public function __construct(
        protected Request $request
    ) {}

    final public function apply(Builder $builder): Builder
    {
        $this->builder = $builder;

        foreach ($this->request->all() as $name => $value) {
            if (method_exists($this, $name)) {
                $value = is_string($value) ? mb_trim($value) : $value;

                if ($value !== '' && $value !== null) {
                    call_user_func([$this, $name], $value);
                }
            }
        }

        return $this->builder;
    }
}
