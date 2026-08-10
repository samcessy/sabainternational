<?php

namespace App\Enums;

/**
 * V1 only offers OneTime and Monthly (docs/product-requirements.md §3);
 * Quarterly/Annual are modeled now so the schema doesn't need to change
 * when those ship.
 */
enum DonationFrequency: string
{
    case OneTime = 'one_time';
    case Monthly = 'monthly';
    case Quarterly = 'quarterly';
    case Annual = 'annual';
}
