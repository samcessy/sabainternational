<?php

namespace Database\Factories;

use App\Enums\ContentStatus;
use App\Models\Campaign;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Campaign>
 */
class CampaignFactory extends Factory
{
    public function definition(): array
    {
        $name = 'General Fund';

        return [
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(1, 100000),
            'description' => fake()->paragraph(),
            'currency' => 'USD',
            'start_date' => now(),
            'suggested_amounts' => [2500, 5000, 10000, 25000, 50000],
            'status' => ContentStatus::Published,
        ];
    }
}
