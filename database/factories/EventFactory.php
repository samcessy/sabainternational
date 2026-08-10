<?php

namespace Database\Factories;

use App\Enums\ContentStatus;
use App\Models\Event;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Event>
 */
class EventFactory extends Factory
{
    public function definition(): array
    {
        $title = fake()->sentence(4);

        return [
            'title' => $title,
            'slug' => Str::slug($title).'-'.fake()->unique()->numberBetween(1, 100000),
            'description' => fake()->paragraph(),
            'start_at' => fake()->dateTimeBetween('now', '+6 months'),
            'location' => fake()->city(),
            'status' => ContentStatus::Draft,
        ];
    }
}
