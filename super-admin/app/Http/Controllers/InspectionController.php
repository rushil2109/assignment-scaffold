<?php

namespace App\Http\Controllers;

use App\Http\Requests\GetRequestAuditRequest;
use App\Http\Requests\ListAuditEventsRequest;
use App\Http\Resources\ApiErrorResponse;
use App\Http\Resources\AuditEventsResource;
use App\Http\Resources\RequestAuditResource;
use App\Models\AuditEvent;
use App\Models\AuditOperation;
use Illuminate\Http\JsonResponse;

class InspectionController extends Controller
{
    public function getRequestAudit(GetRequestAuditRequest $request): RequestAuditResource|JsonResponse
    {
        $data = $request->validated();

        $operation = AuditOperation::where('id', $data['operationId'])
            ->where('user_id', $data['userId'])
            ->first();

        if (! $operation) {
            return ApiErrorResponse::make('Operation not found');
        }

        $events = $operation->events()->orderBy('id')->get()->map(fn ($event) => [
            'at' => $event->created_at->toIso8601String(),
            'type' => $event->type,
            'details' => $event->details,
        ])->all();

        return new RequestAuditResource([
            'audit' => [
                'userId' => $operation->user_id,
                'operationId' => $operation->id,
                'operation' => $operation->operation,
                'status' => $operation->status,
                'events' => $events,
            ],
        ]);
    }

    public function listAuditEvents(ListAuditEventsRequest $request): AuditEventsResource
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

        return new AuditEventsResource(['events' => $events]);
    }
}
