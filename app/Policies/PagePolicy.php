<?php

namespace App\Policies;

use App\Enums\AdminPermission;
use App\Models\Page;
use App\Models\User;

class PagePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->admin_role?->hasPermission(AdminPermission::ViewContent) ?? false;
    }

    public function view(User $user, Page $page): bool
    {
        return $user->admin_role?->hasPermission(AdminPermission::ViewContent) ?? false;
    }

    public function create(User $user): bool
    {
        return $user->admin_role?->hasPermission(AdminPermission::ManageContent) ?? false;
    }

    public function update(User $user, Page $page): bool
    {
        return $user->admin_role?->hasPermission(AdminPermission::ManageContent) ?? false;
    }

    public function delete(User $user, Page $page): bool
    {
        return $user->admin_role?->hasPermission(AdminPermission::ManageContent) ?? false;
    }
}
