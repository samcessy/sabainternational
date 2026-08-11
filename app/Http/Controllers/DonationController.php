<?php

namespace App\Http\Controllers;

use App\Enums\DonationFrequency;
use App\Enums\DonationStatus;
use App\Http\Requests\StoreDonationRequest;
use App\Models\Donation;
use App\Models\Supporter;
use App\Services\Payments\PaymentGatewayInterface;
use App\Services\Payments\Results\SubscriptionResult;
use Illuminate\Http\JsonResponse;

/**
 * Returns JSON, not an Inertia response — the donation form confirms
 * payment client-side via Stripe.js Elements once it has a client_secret
 * (docs/architecture/payment-architecture.md §3), which is a normal
 * fetch/axios call from within an Inertia page, not a full page visit.
 * The webhook (not this endpoint, not the client) is what actually marks
 * a Donation as succeeded — see StripeWebhookController.
 */
class DonationController extends Controller
{
    public function store(StoreDonationRequest $request, PaymentGatewayInterface $gateway): JsonResponse
    {
        $supporter = Supporter::query()->firstOrCreate(
            ['email' => $request->validated('email')],
            ['name' => $request->validated('name')],
        );

        $donation = Donation::create([
            'supporter_id' => $supporter->id,
            'campaign_id' => $request->validated('campaign_id'),
            'program_id' => $request->validated('program_id'),
            'amount_cents' => $request->validated('amount_cents'),
            'currency' => 'USD',
            'frequency' => $request->validated('frequency'),
            'anonymous' => $request->boolean('anonymous'),
            'status' => DonationStatus::Pending,
        ]);

        $result = $donation->frequency === DonationFrequency::Monthly
            ? $gateway->createOrUpdateSubscription($donation, $supporter)
            : $gateway->createOneTimePaymentIntent($donation);

        if ($result instanceof SubscriptionResult) {
            $donation->update(['stripe_subscription_id' => $result->subscriptionId]);
        }

        return response()->json([
            'donation_id' => $donation->id,
            'client_secret' => $result->clientSecret,
        ]);
    }
}
