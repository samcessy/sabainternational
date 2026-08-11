<?php

namespace App\Policies;

use App\Enums\AdminPermission;
use App\Models\DonationTransaction;
use App\Models\User;

/**
 * Read-only from the admin's perspective — transactions are written by the
 * Stripe webhook handler (docs/architecture/payment-architecture.md §5),
 * never created or edited directly through the admin UI.
 */
class DonationTransactionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->admin_role?->hasPermission(AdminPermission::ViewFundraising) ?? false;
    }

    public function view(User $user, DonationTransaction $donationTransaction): bool
    {
        return $user->admin_role?->hasPermission(AdminPermission::ViewFundraising) ?? false;
    }
}
