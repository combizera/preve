<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Tag;
use App\Models\User;
use App\Support\DefaultTags;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Tag>
 */
final class TagFactory extends Factory
{
    protected $model = Tag::class;

    public function definition(): array
    {
        $tag = $this->faker->randomElement(DefaultTags::get());

        return [
            'user_id'     => User::factory(),
            'name'        => $tag['name'],
            'slug'        => Str::slug($tag['name']),
            'description' => $tag['description'],
        ];
    }
}
