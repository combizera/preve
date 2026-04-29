<?php

declare(strict_types=1);

namespace App\Eloquent\Relations;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

/**
 * MorphToMany variant for parents whose integer primary key is stored
 * in a varchar polymorphic column (e.g. `taggables.taggable_id`, which must
 * also accommodate UUID-keyed siblings). Forces a parameterized `whereIn`
 * with string-bound values instead of the default `whereIntegerInRaw`,
 * which inlines values as integer literals and breaks the `varchar = integer`
 * comparison on PostgreSQL.
 *
 * @template TRelatedModel of Model
 * @template TDeclaringModel of Model
 *
 * @extends MorphToMany<TRelatedModel, TDeclaringModel>
 */
final class MorphToManyOnVarcharId extends MorphToMany
{
    /**
     * {@inheritDoc}
     */
    protected function whereInMethod(Model $model, $key): string
    {
        return 'whereIn';
    }

    /**
     * {@inheritDoc}
     */
    protected function getKeys(array $models, $key = null): array
    {
        return array_map(
            static fn (int|string|null $value): mixed => is_int($value) ? (string) $value : $value,
            parent::getKeys($models, $key),
        );
    }
}
