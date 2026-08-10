<?php

namespace Database\Factories;

use App\Models\Supporter;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Supporter>
 */
class SupporterFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
        ];
    }
}
