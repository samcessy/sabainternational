<?php

namespace Database\Factories;

use App\Enums\PaymentGateway;
use App\Enums\TransactionStatus;
use App\Models\Donation;
use App\Models\DonationTransaction;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DonationTransaction>
 */
class DonationTransactionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'donation_id' => Donation::factory(),
            'gateway' => PaymentGateway::Stripe,
            'gateway_reference' => 'pi_'.fake()->unique()->regexify('[A-Za-z0-9]{24}'),
            'status' => TransactionStatus::Succeeded,
        ];
    }
}
