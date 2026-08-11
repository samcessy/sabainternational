<?php

namespace App\Policies;

use App\Enums\AdminPermission;
use App\Models\TeamMember;
use App\Models\User;

class TeamMemberPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->admin_role?->hasPermission(AdminPermission::ViewContent) ?? false;
    }

    public function view(User $user, TeamMember $teamMember): bool
    {
        return $user->admin_role?->hasPermission(AdminPermission::ViewContent) ?? false;
    }

    public function create(User $user): bool
    {
        return $user->admin_role?->hasPermission(AdminPermission::ManageContent) ?? false;
    }

    public function update(User $user, TeamMember $teamMember): bool
    {
        return $user->admin_role?->hasPermission(AdminPermission::ManageContent) ?? false;
    }

    public function delete(User $user, TeamMember $teamMember): bool
    {
        return $user->admin_role?->hasPermission(AdminPermission::ManageContent) ?? false;
    }
}
