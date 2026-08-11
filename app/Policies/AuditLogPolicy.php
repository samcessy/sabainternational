<?php

namespace App\Policies;

use App\Enums\AdminPermission;
use App\Models\AuditLog;
use App\Models\User;

/**
 * View-only — audit logs are written by the system, never created, edited,
 * or deleted through the admin UI. See docs/architecture/database-erd.md §1.
 */
class AuditLogPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->admin_role?->hasPermission(AdminPermission::ViewAuditLogs) ?? false;
    }

    public function view(User $user, AuditLog $auditLog): bool
    {
        return $user->admin_role?->hasPermission(AdminPermission::ViewAuditLogs) ?? false;
    }
}
