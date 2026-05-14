<?php

namespace App\Services;

use App\Models\AuditEvent;
use App\Models\AuditOperation;
use Illuminate\Support\Str;

class AuditService
{
    public function startOperation(string $userId, string $operation): string
    {
        $operationId = Str::uuid()->toString();

        AuditOperation::create([
            'id' => $operationId,
            'user_id' => $userId,
            'operation' => $operation,
            'status' => 'pending',
            'created_at' => now(),
        ]);

        return $operationId;
    }

    public function recordEvent(string $operationId, string $type, ?array $details = null): void
    {
        AuditEvent::create([
            'operation_id' => $operationId,
            'type' => $type,
            'details' => $details,
            'created_at' => now(),
        ]);
    }

    public function completeOperation(string $operationId, string $status): void
    {
        AuditOperation::where('id', $operationId)->update(['status' => $status]);
    }
}
