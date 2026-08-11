<?php

use App\Enums\DonationFrequency;
use App\Enums\DonationStatus;
use App\Models\Donation;
use App\Models\Supporter;
use App\Services\Payments\PaymentGatewayInterface;
use Tests\Support\FakePaymentGateway;

beforeEach(function () {
    $this->app->bind(PaymentGatewayInterface::class, FakePaymentGateway::class);
});

function validDonationPayload(array $overrides = []): array
{
    return array_merge([
        'amount_cents' => 5000,
        'frequency' => 'one_time',
        'name' => 'Jane Donor',
        'email' => 'jane@example.com',
        'anonymous' => false,
    ], $overrides);
}

test('a one-time donation creates a pending donation and returns a client secret', function () {
    $response = $this->postJson(route('donations.store'), validDonationPayload());

    $response->assertOk()->assertJsonStructure(['donation_id', 'client_secret']);

    $donation = Donation::query()->findOrFail($response->json('donation_id'));
    expect($donation->status)->toBe(DonationStatus::Pending)
        ->and($donation->frequency)->toBe(DonationFrequency::OneTime)
        ->and($donation->amount_cents)->toBe(5000)
        ->and($donation->stripe_subscription_id)->toBeNull();
});

test('a monthly donation creates or reuses a stripe customer and stores the subscription id', function () {
    $response = $this->postJson(route('donations.store'), validDonationPayload(['frequency' => 'monthly']));

    $response->assertOk();

    $donation = Donation::query()->findOrFail($response->json('donation_id'));
    expect($donation->frequency)->toBe(DonationFrequency::Monthly)
        ->and($donation->stripe_subscription_id)->not->toBeNull()
        ->and($donation->supporter->stripe_customer_id)->not->toBeNull();
});

test('donations reuse an existing supporter record by email', function () {
    $existing = Supporter::factory()->create(['email' => 'jane@example.com']);

    $this->postJson(route('donations.store'), validDonationPayload());

    expect(Supporter::query()->count())->toBe(1)
        ->and(Donation::query()->first()->supporter_id)->toBe($existing->id);
});

test('donation amount below the minimum is rejected', function () {
    $this->postJson(route('donations.store'), validDonationPayload(['amount_cents' => 50]))
        ->assertJsonValidationErrors('amount_cents');
});

test('quarterly and annual frequencies are rejected in v1', function () {
    $this->postJson(route('donations.store'), validDonationPayload(['frequency' => 'quarterly']))
        ->assertJsonValidationErrors('frequency');
});

test('donation attempts are rate limited to 5 per hour per ip', function () {
    foreach (range(1, 5) as $_) {
        $this->postJson(route('donations.store'), validDonationPayload(['email' => fake()->unique()->safeEmail()]))
            ->assertOk();
    }

    $this->postJson(route('donations.store'), validDonationPayload())->assertStatus(429);
});
