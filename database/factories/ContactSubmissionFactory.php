<?php

namespace Database\Factories;

use App\Enums\ContactSubject;
use App\Enums\SubmissionStatus;
use App\Models\ContactSubmission;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ContactSubmission>
 */
class ContactSubmissionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->safeEmail(),
            'subject' => ContactSubject::General,
            'message' => fake()->paragraph(),
            'status' => SubmissionStatus::New,
        ];
    }
}
