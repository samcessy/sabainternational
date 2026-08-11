<?php

namespace App\Policies;

use App\Enums\AdminPermission;
use App\Models\User;
use App\Models\VolunteerApplication;

class VolunteerApplicationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->admin_role?->hasPermission(AdminPermission::ViewEngagement) ?? false;
    }

    public function view(User $user, VolunteerApplication $volunteerApplication): bool
    {
        return $user->admin_role?->hasPermission(AdminPermission::ViewEngagement) ?? false;
    }

    public function update(User $user, VolunteerApplication $volunteerApplication): bool
    {
        return $user->admin_role?->hasPermission(AdminPermission::ManageEngagement) ?? false;
    }

    public function delete(User $user, VolunteerApplication $volunteerApplication): bool
    {
        return $user->admin_role?->hasPermission(AdminPermission::ManageEngagement) ?? false;
    }
}
