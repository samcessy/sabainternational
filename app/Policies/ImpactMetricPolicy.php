<?php

namespace App\Policies;

use App\Enums\AdminPermission;
use App\Models\ImpactMetric;
use App\Models\User;

/**
 * Impact metrics use ViewImpactData/ManageImpactData, distinct from the
 * generic content permissions — matches the "Manage impact metrics &
 * reports" row in docs/architecture/authorization-model.md §3, where
 * Viewer gets read access but Finance Manager does not (impact data isn't
 * financial data).
 */
class ImpactMetricPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->admin_role?->hasPermission(AdminPermission::ViewImpactData) ?? false;
    }

    public function view(User $user, ImpactMetric $impactMetric): bool
    {
        return $user->admin_role?->hasPermission(AdminPermission::ViewImpactData) ?? false;
    }

    public function create(User $user): bool
    {
        return $user->admin_role?->hasPermission(AdminPermission::ManageImpactData) ?? false;
    }

    public function update(User $user, ImpactMetric $impactMetric): bool
    {
        return $user->admin_role?->hasPermission(AdminPermission::ManageImpactData) ?? false;
    }

    public function delete(User $user, ImpactMetric $impactMetric): bool
    {
        return $user->admin_role?->hasPermission(AdminPermission::ManageImpactData) ?? false;
    }
}
