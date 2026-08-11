<?php

namespace App\Http\Controllers\Api;

use App\Actions\Payments\ProcessStripeWebhookEvent;
use App\Http\Controllers\Controller;
use App\Services\Payments\PaymentGatewayInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Stripe\Event;
use Stripe\Exception\SignatureVerificationException;
use UnexpectedValueException;

/**
 * Not "public" in the request-origin sense — this is Stripe calling us, not
 * a browser client. Verified via signature, not user auth. See
 * docs/architecture/authentication.md §4, docs/architecture/api-architecture.md §1.
 */
class StripeWebhookController extends Controller
{
    public function __invoke(
        Request $request,
        PaymentGatewayInterface $gateway,
        ProcessStripeWebhookEvent $processor,
    ): JsonResponse {
        try {
            $event = $gateway->verifyWebhookSignature(
                $request->getContent(),
                (string) $request->header('Stripe-Signature'),
            );
        } catch (SignatureVerificationException|UnexpectedValueException) {
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        if (! $event instanceof Event) {
            return response()->json(['error' => 'Unexpected event payload'], 400);
        }

        $processor->handle($event);

        return response()->json(['received' => true]);
    }
}
