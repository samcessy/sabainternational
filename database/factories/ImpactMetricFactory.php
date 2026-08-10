<?php

namespace Database\Factories;

use App\Models\ImpactMetric;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ImpactMetric>
 */
class ImpactMetricFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->randomElement(['Students Enrolled', 'Meals Served', 'Children Housed']),
            'unit' => fake()->randomElement(['students', 'meals', 'children']),
        ];
    }
}
