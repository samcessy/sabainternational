<?php

namespace App\Policies;

use App\Enums\AdminPermission;
use App\Models\Donation;
use App\Models\User;

/**
 * No delete ability at all — a donation is financial history. "Change
 * donation status" (e.g. mark refunded) happens via update, driven by the
 * Stripe webhook per docs/architecture/payment-architecture.md §6, not by
 * deleting the record.
 */
class DonationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->admin_role?->hasPermission(AdminPermission::ViewFundraising) ?? false;
    }

    public function view(User $user, Donation $donation): bool
    {
        return $user->admin_role?->hasPermission(AdminPermission::ViewFundraising) ?? false;
    }

    public function create(User $user): bool
    {
        return $user->admin_role?->hasPermission(AdminPermission::ManageFundraising) ?? false;
    }

    public function update(User $user, Donation $donation): bool
    {
        return $user->admin_role?->hasPermission(AdminPermission::ManageFundraising) ?? false;
    }

    public function export(User $user): bool
    {
        return $user->admin_role?->hasPermission(AdminPermission::ExportDonorData) ?? false;
    }
}
