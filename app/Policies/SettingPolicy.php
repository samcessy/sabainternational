<?php

namespace App\Policies;

use App\Enums\AdminPermission;
use App\Models\Setting;
use App\Models\User;

class SettingPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->admin_role?->hasPermission(AdminPermission::ManageSettings) ?? false;
    }

    public function view(User $user, Setting $setting): bool
    {
        return $user->admin_role?->hasPermission(AdminPermission::ManageSettings) ?? false;
    }

    public function update(User $user, Setting $setting): bool
    {
        return $user->admin_role?->hasPermission(AdminPermission::ManageSettings) ?? false;
    }
}
