<?php

namespace App\Services\Payments;

use App\Models\Donation;
use App\Models\DonationTransaction;
use App\Models\Supporter;
use App\Services\Payments\Results\PaymentIntentResult;
use App\Services\Payments\Results\RefundResult;
use App\Services\Payments\Results\SubscriptionResult;

/**
 * Per docs/architecture/payment-architecture.md §2 — StripeGateway is the
 * only V1 implementation. This interface exists so PayPal/MPesa are
 * additive later (saba.md §8.3), not because a second gateway is being
 * built now.
 */
interface PaymentGatewayInterface
{
    public function createOneTimePaymentIntent(Donation $donation): PaymentIntentResult;

    public function createOrUpdateSubscription(Donation $donation, Supporter $supporter): SubscriptionResult;

    public function refund(DonationTransaction $transaction): RefundResult;

    /**
     * Verifies the webhook signature and returns the parsed event. Throws
     * if the signature is invalid — callers should turn that into a 400,
     * never process an unverified payload.
     */
    public function verifyWebhookSignature(string $payload, string $signature): object;
}
