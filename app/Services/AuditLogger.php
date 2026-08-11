<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Records privileged admin actions (saba.md §10.3). Shared by every admin
 * controller rather than writing AuditLog::create() inline in each one, so
 * entity_type stays consistent (snake_case model basename, e.g. "program",
 * matching AuditLogFactory's convention) and every write captures the
 * request's IP/user agent the same way.
 */
class AuditLogger
{
    /**
     * @param  array<string, mixed>  $oldValues
     * @param  array<string, mixed>  $newValues
     */
    public function log(User $user, string $action, Model $entity, array $oldValues = [], array $newValues = []): AuditLog
    {
        return AuditLog::create([
            'user_id' => $user->id,
            'action' => $action,
            'entity_type' => Str::snake(class_basename($entity)),
            'entity_id' => $entity->getKey(),
            'old_values' => $oldValues ?: null,
            'new_values' => $newValues ?: null,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
