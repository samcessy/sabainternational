<?php

namespace Database\Factories;

use App\Enums\VerificationStatus;
use App\Models\ImpactMetric;
use App\Models\ImpactMetricValue;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ImpactMetricValue>
 */
class ImpactMetricValueFactory extends Factory
{
    public function definition(): array
    {
        return [
            'metric_id' => ImpactMetric::factory(),
            'value' => fake()->numberBetween(10, 500),
            'time_period' => now()->year.' School Year',
            'data_source' => fake()->company().' program records',
            'verification_status' => VerificationStatus::Unverified,
            'last_updated_at' => now(),
        ];
    }

    public function verified(): static
    {
        return $this->state(fn () => ['verification_status' => VerificationStatus::Verified]);
    }
}
