<?php

namespace App\Http\Controllers;

use App\Contracts\AdminApiInterface;
use App\Models\AuditOperation;
use App\Models\Member;
use App\Services\AuditService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PublicApiController extends Controller
{
    public function __construct(
        private AdminApiInterface $adminApi,
        private AuditService $auditService,
    ) {}

    public function createMember(Request $request): JsonResponse
    {
        $data = $request->json()->all();

        if (empty($data['userId'])) {
            return new JsonResponse(['ok' => false, 'error' => 'userId is required']);
        }

        if (! isset($data['investmentProfile']) || ! is_array($data['investmentProfile'])) {
            return new JsonResponse(['ok' => false, 'error' => 'investmentProfile is required']);
        }

        $validationError = $this->validateAllocations($data['investmentProfile']);
        if ($validationError) {
            return new JsonResponse(['ok' => false, 'error' => $validationError]);
        }

        $existing = Member::where('user_id', $data['userId'])->first();
        if ($existing) {
            $operationId = AuditOperation::where('user_id', $data['userId'])
                ->where('operation', 'createMember')
                ->value('id');

            return new JsonResponse([
                'ok' => true,
                'memberId' => $existing->id,
                'accountId' => $existing->account->account_id,
                'operationId' => $operationId,
            ]);
        }

        return DB::transaction(function () use ($data) {
            $adminId = Str::uuid()->toString();

            $result = $this->adminApi->createMember($adminId, $data);

            $this->adminApi->setInvestmentProfile($adminId, [
                'allocations' => $data['investmentProfile'],
            ]);

            $operationId = $this->auditService->startOperation($data['userId'], 'createMember');
            $this->auditService->recordEvent($operationId, 'member_created', [
                'memberId' => $result['memberId'],
                'accountId' => $result['accountId'],
            ]);
            $this->auditService->completeOperation($operationId, 'success');

            return new JsonResponse([
                'ok' => true,
                'memberId' => $result['memberId'],
                'accountId' => $result['accountId'],
                'operationId' => $operationId,
            ]);
        });
    }

    private function validateAllocations(array $allocations): ?string
    {
        $validCodes = ['Cash', 'Conservative', 'Balanced', 'Growth', 'HighGrowth'];
        $seen = [];
        $sum = 0;

        foreach ($allocations as $allocation) {
            if (! isset($allocation['assetCode']) || ! isset($allocation['percentage'])) {
                return 'Each allocation must have assetCode and percentage';
            }

            if (! in_array($allocation['assetCode'], $validCodes, true)) {
                return "Invalid asset code: {$allocation['assetCode']}";
            }

            if (in_array($allocation['assetCode'], $seen, true)) {
                return "Duplicate asset code: {$allocation['assetCode']}";
            }
            $seen[] = $allocation['assetCode'];

            $percentage = $allocation['percentage'];
            $parts = explode('.', (string) $percentage);
            if (isset($parts[1]) && strlen($parts[1]) > 2) {
                return 'Percentages must have at most 2 decimal places';
            }

            $sum = bcadd($sum, (string) $percentage, 2);
        }

        if (bccomp($sum, '100.00', 2) !== 0) {
            return 'Allocations must sum to exactly 100.00';
        }

        return null;
    }
}
