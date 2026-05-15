<?php

namespace App\Http\Controllers;

use App\Http\Requests\GetRequestAuditRequest;
use App\Http\Requests\ListAuditEventsRequest;
use App\Models\AuditEvent;
use App\Models\AuditOperation;
use Illuminate\Http\JsonResponse;

class InspectionController extends Controller
{
    public function getRequestAudit(GetRequestAuditRequest $request): JsonResponse
    {
        $data = $request->validated();

        $operation = AuditOperation::where('id', $data['operationId'])
            ->where('user_id', $data['userId'])
            ->first();

        if (! $operation) {
            return new JsonResponse(['ok' => false, 'error' => 'Operation not found']);
        }

        $events = $operation->events()->orderBy('id')->get()->map(fn ($event) => [
            'at' => $event->created_at->toIso8601String(),
            'type' => $event->type,
            'details' => $event->details,
        ])->all();

        return new JsonResponse([
            'ok' => true,
            'audit' => [
                'userId' => $operation->user_id,
                'operationId' => $operation->id,
                'operation' => $operation->operation,
                'status' => $operation->status,
                'events' => $events,
            ],
        ]);
    }

    public function listAuditEvents(ListAuditEventsRequest $request): JsonResponse
    {
        $data = $request->validated();

        $events = AuditEvent::whereHas('operation', fn ($q) => $q->where('user_id', $data['userId']))
            ->orderBy('id')
            ->get()
            ->map(fn ($event) => [
                'at' => $event->created_at->toIso8601String(),
                'type' => $event->type,
                'details' => $event->details,
            ])->all();

        return new JsonResponse(['ok' => true, 'events' => $events]);
    }
}
