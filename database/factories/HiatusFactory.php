<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Hiatus;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Hiatus>
 */
final class HiatusFactory extends Factory
{
    protected $model = Hiatus::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $start = CarbonImmutable::instance($this->faker->dateTimeBetween('-120 days', '-60 days'));

        return [
            'user_id'    => User::factory(),
            'started_at' => $start->toDateString(),
            'ended_at'   => $start->addDays(30)->toDateString(),
            'note'       => $this->faker->optional()->sentence(3),
        ];
    }

    public function open(): self
    {
        return $this->state(['ended_at' => null]);
    }

    public function reconciled(): self
    {
        return $this->state(['reconciled_at' => CarbonImmutable::now()]);
    }
}
