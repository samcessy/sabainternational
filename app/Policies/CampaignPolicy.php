<?php

namespace App\Policies;

use App\Enums\AdminPermission;
use App\Models\Campaign;
use App\Models\User;

class CampaignPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->admin_role?->hasPermission(AdminPermission::ViewFundraising) ?? false;
    }

    public function view(User $user, Campaign $campaign): bool
    {
        return $user->admin_role?->hasPermission(AdminPermission::ViewFundraising) ?? false;
    }

    public function create(User $user): bool
    {
        return $user->admin_role?->hasPermission(AdminPermission::ManageFundraising) ?? false;
    }

    public function update(User $user, Campaign $campaign): bool
    {
        return $user->admin_role?->hasPermission(AdminPermission::ManageFundraising) ?? false;
    }

    public function delete(User $user, Campaign $campaign): bool
    {
        return $user->admin_role?->hasPermission(AdminPermission::ManageFundraising) ?? false;
    }
}
