<?php

use App\Enums\DonationStatus;
use App\Models\Donation;
use App\Models\Supporter;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;

function signedStripeWebhookHeader(string $payload, string $secret): string
{
    $timestamp = time();
    $signature = hash_hmac('sha256', "{$timestamp}.{$payload}", $secret);

    return "t={$timestamp},v1={$signature}";
}

beforeEach(function () {
    config(['services.stripe.webhook_secret' => 'whsec_test_secret']);
});

test('a webhook request with an invalid signature is rejected', function () {
    $payload = json_encode(['type' => 'payment_intent.succeeded', 'data' => ['object' => ['id' => 'pi_123']]]);

    $response = $this->call('POST', '/api/v1/payments/webhook', [], [], [], [
        'HTTP_Stripe-Signature' => 't='.time().',v1=not-a-real-signature',
        'CONTENT_TYPE' => 'application/json',
    ], $payload);

    $response->assertStatus(400);
});

test('a webhook request with a valid signature updates the donation on payment_intent.succeeded', function () {
    Mail::fake();
    Notification::fake();

    $supporter = Supporter::factory()->create();
    $donation = Donation::factory()->for($supporter)->create(['status' => DonationStatus::Pending]);

    $payload = json_encode([
        'id' => 'evt_test',
        'type' => 'payment_intent.succeeded',
        'data' => [
            'object' => [
                'id' => 'pi_test_123',
                'metadata' => ['donation_id' => (string) $donation->id],
            ],
        ],
    ]);

    $secret = config('services.stripe.webhook_secret');

    $response = $this->call('POST', '/api/v1/payments/webhook', [], [], [], [
        'HTTP_Stripe-Signature' => signedStripeWebhookHeader($payload, $secret),
        'CONTENT_TYPE' => 'application/json',
    ], $payload);

    $response->assertOk();
    expect($donation->fresh()->status)->toBe(DonationStatus::Succeeded);
    $this->assertDatabaseHas('donation_transactions', [
        'donation_id' => $donation->id,
        'gateway_reference' => 'pi_test_123',
    ]);
});
