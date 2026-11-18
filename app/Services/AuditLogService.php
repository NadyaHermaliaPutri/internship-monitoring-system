<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;
use Carbon\Carbon;

class AuditLogService
{
    /**
     * Mencatat jejak audit penting ke tabel audit_logs.
     */
    public function log(
        string $action,
        string $entityType,
        ?int $entityId = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?User $actor = null,
        ?string $ipAddress = null
    ): AuditLog {
        $actorId = $actor ? $actor->id : (auth()->id() ?: null);
        $ip = $ipAddress ?: request()->ip();

        return AuditLog::create([
            'actor_user_id' => $actorId,
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => $ip,
            'created_at' => Carbon::now(config('wantrace.timezone', 'Asia/Jakarta')),
        ]);
    }
}
