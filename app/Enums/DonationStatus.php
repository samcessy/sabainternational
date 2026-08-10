<?php

namespace App\Enums;

/**
 * Donation.status — flipped by the Stripe webhook, never the client
 * confirmation callback. See docs/architecture/payment-architecture.md §3.
 */
enum DonationStatus: string
{
    case Pending = 'pending';
    case Succeeded = 'succeeded';
    case Failed = 'failed';
}
