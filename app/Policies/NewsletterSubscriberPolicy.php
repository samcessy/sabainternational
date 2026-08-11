<?php

namespace App\Policies;

use App\Enums\AdminPermission;
use App\Models\NewsletterSubscriber;
use App\Models\User;

class NewsletterSubscriberPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->admin_role?->hasPermission(AdminPermission::ViewEngagement) ?? false;
    }

    public function view(User $user, NewsletterSubscriber $newsletterSubscriber): bool
    {
        return $user->admin_role?->hasPermission(AdminPermission::ViewEngagement) ?? false;
    }

    public function update(User $user, NewsletterSubscriber $newsletterSubscriber): bool
    {
        return $user->admin_role?->hasPermission(AdminPermission::ManageEngagement) ?? false;
    }

    public function delete(User $user, NewsletterSubscriber $newsletterSubscriber): bool
    {
        return $user->admin_role?->hasPermission(AdminPermission::ManageEngagement) ?? false;
    }
}
