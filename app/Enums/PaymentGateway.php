<?php

namespace App\Enums;

/**
 * Stripe is the only V1 gateway (docs/architecture/payment-architecture.md
 * §2) — PayPal/MPesa are added here only when actually built, not stubbed
 * speculatively now.
 */
enum PaymentGateway: string
{
    case Stripe = 'stripe';
}
