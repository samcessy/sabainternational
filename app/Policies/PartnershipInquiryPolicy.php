<?php

namespace App\Policies;

use App\Enums\AdminPermission;
use App\Models\PartnershipInquiry;
use App\Models\User;

class PartnershipInquiryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->admin_role?->hasPermission(AdminPermission::ViewEngagement) ?? false;
    }

    public function view(User $user, PartnershipInquiry $partnershipInquiry): bool
    {
        return $user->admin_role?->hasPermission(AdminPermission::ViewEngagement) ?? false;
    }

    public function update(User $user, PartnershipInquiry $partnershipInquiry): bool
    {
        return $user->admin_role?->hasPermission(AdminPermission::ManageEngagement) ?? false;
    }

    public function delete(User $user, PartnershipInquiry $partnershipInquiry): bool
    {
        return $user->admin_role?->hasPermission(AdminPermission::ManageEngagement) ?? false;
    }
}
