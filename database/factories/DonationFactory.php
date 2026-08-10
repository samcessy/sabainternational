<?php

namespace Database\Factories;

use App\Enums\DonationFrequency;
use App\Enums\DonationStatus;
use App\Models\Donation;
use App\Models\Supporter;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Donation>
 */
class DonationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'supporter_id' => Supporter::factory(),
            'amount_cents' => fake()->randomElement([2500, 5000, 10000, 25000, 50000]),
            'currency' => 'USD',
            'frequency' => DonationFrequency::OneTime,
            'anonymous' => false,
            'status' => DonationStatus::Pending,
        ];
    }

    public function succeeded(): static
    {
        return $this->state(fn () => ['status' => DonationStatus::Succeeded]);
    }

    public function monthly(): static
    {
        return $this->state(fn () => ['frequency' => DonationFrequency::Monthly]);
    }
}
