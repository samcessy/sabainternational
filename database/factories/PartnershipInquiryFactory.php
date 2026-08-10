<?php

namespace Database\Factories;

use App\Enums\SubmissionStatus;
use App\Models\PartnershipInquiry;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PartnershipInquiry>
 */
class PartnershipInquiryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'organization_name' => fake()->company(),
            'contact_name' => fake()->name(),
            'email' => fake()->safeEmail(),
            'details' => fake()->paragraph(),
            'status' => SubmissionStatus::New,
        ];
    }
}
