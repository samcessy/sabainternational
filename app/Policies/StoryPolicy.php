<?php

namespace App\Policies;

use App\Enums\AdminPermission;
use App\Models\Story;
use App\Models\User;

class StoryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->admin_role?->hasPermission(AdminPermission::ViewContent) ?? false;
    }

    public function view(User $user, Story $story): bool
    {
        return $user->admin_role?->hasPermission(AdminPermission::ViewContent) ?? false;
    }

    public function create(User $user): bool
    {
        return $user->admin_role?->hasPermission(AdminPermission::ManageContent) ?? false;
    }

    public function update(User $user, Story $story): bool
    {
        return $user->admin_role?->hasPermission(AdminPermission::ManageContent) ?? false;
    }

    public function delete(User $user, Story $story): bool
    {
        return $user->admin_role?->hasPermission(AdminPermission::ManageContent) ?? false;
    }
}
