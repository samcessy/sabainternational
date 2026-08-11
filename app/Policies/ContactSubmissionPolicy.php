<?php

namespace App\Policies;

use App\Enums\AdminPermission;
use App\Models\ContactSubmission;
use App\Models\User;

class ContactSubmissionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->admin_role?->hasPermission(AdminPermission::ViewEngagement) ?? false;
    }

    public function view(User $user, ContactSubmission $contactSubmission): bool
    {
        return $user->admin_role?->hasPermission(AdminPermission::ViewEngagement) ?? false;
    }

    public function update(User $user, ContactSubmission $contactSubmission): bool
    {
        return $user->admin_role?->hasPermission(AdminPermission::ManageEngagement) ?? false;
    }

    public function delete(User $user, ContactSubmission $contactSubmission): bool
    {
        return $user->admin_role?->hasPermission(AdminPermission::ManageEngagement) ?? false;
    }
}
