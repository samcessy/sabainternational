<?php

namespace App\Policies;

use App\Enums\AdminPermission;
use App\Models\Program;
use App\Models\User;

class ProgramPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->admin_role?->hasPermission(AdminPermission::ViewContent) ?? false;
    }

    public function view(User $user, Program $program): bool
    {
        return $user->admin_role?->hasPermission(AdminPermission::ViewContent) ?? false;
    }

    public function create(User $user): bool
    {
        return $user->admin_role?->hasPermission(AdminPermission::ManageContent) ?? false;
    }

    public function update(User $user, Program $program): bool
    {
        return $user->admin_role?->hasPermission(AdminPermission::ManageContent) ?? false;
    }

    public function delete(User $user, Program $program): bool
    {
        return $user->admin_role?->hasPermission(AdminPermission::ManageContent) ?? false;
    }
}
