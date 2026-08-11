<?php

namespace App\Services\Payments;

use App\Models\Donation;
use App\Models\DonationTransaction;
use App\Models\Setting;
use App\Models\Supporter;
use App\Services\Payments\Results\PaymentIntentResult;
use App\Services\Payments\Results\RefundResult;
use App\Services\Payments\Results\SubscriptionResult;
use RuntimeException;
use Stripe\Invoice;
use Stripe\StripeClient;
use Stripe\Webhook;

/**
 * See docs/architecture/payment-architecture.md §3 for the full flow this
 * implements. Idempotency keys on outbound requests are derived from the
 * Donation's id, protecting against double-submit creating duplicate
 * PaymentIntents/Subscriptions for one donor action.
 */
class StripeGateway implements PaymentGatewayInterface
{
    private ?StripeClient $client = null;

    public function __construct(?StripeClient $client = null)
    {
        $this->client = $client;
    }

    /**
     * Lazily constructed — verifyWebhookSignature() never needs the API
     * secret key, only the webhook secret. Requiring both eagerly would
     * mean the webhook endpoint couldn't function in any environment
     * where only the webhook secret is configured.
     */
    private function client(): StripeClient
    {
        return $this->client ??= new StripeClient(config('services.stripe.secret'));
    }

    public function createOneTimePaymentIntent(Donation $donation): PaymentIntentResult
    {
        $paymentIntent = $this->client()->paymentIntents->create(
            [
                'amount' => $donation->amount_cents,
                'currency' => strtolower($donation->currency),
                'metadata' => ['donation_id' => (string) $donation->id],
            ],
            ['idempotency_key' => "donation-{$donation->id}-payment-intent"],
        );

        return new PaymentIntentResult(
            paymentIntentId: $paymentIntent->id,
            clientSecret: $paymentIntent->client_secret,
        );
    }

    public function createOrUpdateSubscription(Donation $donation, Supporter $supporter): SubscriptionResult
    {
        if (! $supporter->stripe_customer_id) {
            $customer = $this->client()->customers->create([
                'name' => $supporter->name,
                'email' => $supporter->email,
            ]);

            $supporter->update(['stripe_customer_id' => $customer->id]);
        }

        $subscription = $this->client()->subscriptions->create(
            [
                'customer' => $supporter->stripe_customer_id,
                'items' => [[
                    'price_data' => [
                        'currency' => strtolower($donation->currency),
                        'product' => $this->monthlyDonationProductId(),
                        'unit_amount' => $donation->amount_cents,
                        'recurring' => ['interval' => 'month'],
                    ],
                ]],
                'payment_behavior' => 'default_incomplete',
                'payment_settings' => ['save_default_payment_method' => 'on_subscription'],
                'expand' => ['latest_invoice'],
                'metadata' => ['donation_id' => (string) $donation->id],
            ],
            ['idempotency_key' => "donation-{$donation->id}-subscription"],
        );

        $invoice = $subscription->latest_invoice;

        // This API version replaced Invoice.payment_intent with
        // confirmation_secret — per Stripe's own field description, it
        // "contains the client_secret of the PaymentIntent that Stripe
        // creates during invoice finalization," i.e. exactly what the
        // client needs to confirm payment via Stripe.js Elements.
        if (! $invoice instanceof Invoice || ! $invoice->confirmation_secret) {
            throw new RuntimeException('Stripe did not return a confirmation secret for the new subscription.');
        }

        return new SubscriptionResult(
            subscriptionId: $subscription->id,
            clientSecret: $invoice->confirmation_secret->client_secret,
        );
    }

    /**
     * Subscription line items need a real Product id, not the inline
     * `product_data` shorthand that one-off Invoice items/Checkout Sessions
     * support — Stripe's Subscription API doesn't accept it. Rather than
     * pre-provisioning a Product by hand, it's created once on first use
     * and its id cached in `settings`.
     */
    private function monthlyDonationProductId(): string
    {
        $cached = Setting::get('stripe_monthly_donation_product_id');

        if (is_string($cached) && $cached !== '') {
            return $cached;
        }

        $product = $this->client()->products->create([
            'name' => 'Saba International — Monthly Donation',
        ]);

        Setting::set('stripe_monthly_donation_product_id', $product->id);

        return $product->id;
    }

    public function refund(DonationTransaction $transaction): RefundResult
    {
        $refund = $this->client()->refunds->create([
            'payment_intent' => $transaction->gateway_reference,
        ]);

        return new RefundResult(refundId: $refund->id, status: $refund->status);
    }

    public function verifyWebhookSignature(string $payload, string $signature): object
    {
        return Webhook::constructEvent(
            $payload,
            $signature,
            (string) config('services.stripe.webhook_secret'),
        );
    }
}
