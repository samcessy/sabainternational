<?php

namespace Tests\Support;

use App\Models\Donation;
use App\Models\DonationTransaction;
use App\Models\Supporter;
use App\Services\Payments\PaymentGatewayInterface;
use App\Services\Payments\Results\PaymentIntentResult;
use App\Services\Payments\Results\RefundResult;
use App\Services\Payments\Results\SubscriptionResult;
use Stripe\Event;

/**
 * Test double bound over PaymentGatewayInterface so feature tests never
 * make real Stripe API calls. See docs/architecture/payment-architecture.md.
 */
class FakePaymentGateway implements PaymentGatewayInterface
{
    public function createOneTimePaymentIntent(Donation $donation): PaymentIntentResult
    {
        return new PaymentIntentResult(
            paymentIntentId: 'pi_fake_'.$donation->id,
            clientSecret: 'pi_fake_'.$donation->id.'_secret_test',
        );
    }

    public function createOrUpdateSubscription(Donation $donation, Supporter $supporter): SubscriptionResult
    {
        if (! $supporter->stripe_customer_id) {
            $supporter->update(['stripe_customer_id' => 'cus_fake_'.$supporter->id]);
        }

        return new SubscriptionResult(
            subscriptionId: 'sub_fake_'.$donation->id,
            clientSecret: 'pi_fake_'.$donation->id.'_secret_test',
        );
    }

    public function refund(DonationTransaction $transaction): RefundResult
    {
        return new RefundResult(refundId: 're_fake_'.$transaction->id, status: 'succeeded');
    }

    public function verifyWebhookSignature(string $payload, string $signature): object
    {
        return Event::constructFrom(json_decode($payload, true));
    }
}
