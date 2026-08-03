<?php

namespace App\Services;

use App\Models\PermohonanLog;
use Illuminate\Support\Facades\Log;

class QiLog
{
    public static function audit(string $action, int $permohonanId, int $actorId, ?string $notes = null, ?string $statusFrom = null, ?string $statusTo = null)
    {
        Log::info("[QI-AUDIT] Action: {$action} on Permohonan #{$permohonanId} by User #{$actorId}");

        if (class_exists(PermohonanLog::class)) {
            return PermohonanLog::create([
                'permohonan_id' => $permohonanId,
                'actor_id' => $actorId,
                'action' => $action,
                'status_from' => $statusFrom,
                'status_to' => $statusTo,
                'notes' => $notes,
            ]);
        }
        return null;
    }
}
