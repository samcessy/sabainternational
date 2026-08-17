<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Read-only — AuditLogPolicy has no create/update/delete abilities at all,
 * since the log is written exclusively by AuditLogger, never through this
 * UI. See docs/architecture/database-erd.md §1.
 */
class AuditLogController extends Controller
{
    public function index(): Response
    {
        $this->authorize('viewAny', AuditLog::class);

        return Inertia::render('admin/audit-logs/Index', [
            'auditLogs' => AuditLog::query()
                ->with('user')
                ->latest('created_at')
                ->paginate(30)
                ->through(fn (AuditLog $auditLog) => [
                    'id' => $auditLog->id,
                    'user_name' => $auditLog->user?->name,
                    'action' => $auditLog->action,
                    'entity_type' => $auditLog->entity_type,
                    'entity_id' => $auditLog->entity_id,
                    'old_values' => $auditLog->old_values,
                    'new_values' => $auditLog->new_values,
                    'ip_address' => $auditLog->ip_address,
                    'user_agent' => $auditLog->user_agent,
                    'created_at' => $auditLog->created_at?->toIso8601String(),
                ]),
        ]);
    }
}
