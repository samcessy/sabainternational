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

    // A recurring donation whose Stripe subscription was cancelled — set
    // on `customer.subscription.deleted`, per
    // docs/architecture/payment-architecture.md §5. Distinct from Failed:
    // the donation may have succeeded many times before cancellation.
    case Cancelled = 'cancelled';
}
