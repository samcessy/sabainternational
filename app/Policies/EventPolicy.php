<?php

namespace App\Policies;

use App\Enums\AdminPermission;
use App\Models\Event;
use App\Models\User;

class EventPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->admin_role?->hasPermission(AdminPermission::ViewContent) ?? false;
    }

    public function view(User $user, Event $event): bool
    {
        return $user->admin_role?->hasPermission(AdminPermission::ViewContent) ?? false;
    }

    public function create(User $user): bool
    {
        return $user->admin_role?->hasPermission(AdminPermission::ManageContent) ?? false;
    }

    public function update(User $user, Event $event): bool
    {
        return $user->admin_role?->hasPermission(AdminPermission::ManageContent) ?? false;
    }

    public function delete(User $user, Event $event): bool
    {
        return $user->admin_role?->hasPermission(AdminPermission::ManageContent) ?? false;
    }
}
