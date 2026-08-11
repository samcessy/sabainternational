<?php

namespace App\Policies;

use App\Enums\AdminPermission;
use App\Models\Supporter;
use App\Models\User;

class SupporterPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->admin_role?->hasPermission(AdminPermission::ViewFundraising) ?? false;
    }

    public function view(User $user, Supporter $supporter): bool
    {
        return $user->admin_role?->hasPermission(AdminPermission::ViewFundraising) ?? false;
    }

    public function update(User $user, Supporter $supporter): bool
    {
        return $user->admin_role?->hasPermission(AdminPermission::ManageFundraising) ?? false;
    }

    public function export(User $user): bool
    {
        return $user->admin_role?->hasPermission(AdminPermission::ExportDonorData) ?? false;
    }
}
