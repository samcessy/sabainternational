<?php

namespace Database\Factories;

use App\Enums\SubmissionStatus;
use App\Models\VolunteerApplication;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<VolunteerApplication>
 */
class VolunteerApplicationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->safeEmail(),
            'details' => fake()->paragraph(),
            'status' => SubmissionStatus::New,
        ];
    }
}
