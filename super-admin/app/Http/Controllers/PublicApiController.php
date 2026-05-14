<?php

namespace App\Http\Controllers;

use App\Contracts\AdminApiInterface;
use App\Http\Requests\CreateMemberRequest;
use App\Http\Resources\ApiErrorResponse;
use App\Http\Resources\CreateMemberResource;
use App\Models\AuditOperation;
use App\Models\Member;
use App\Services\AuditService;
use App\Traits\ResolvesMember;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PublicApiController extends Controller
{
    use ResolvesMember;

    public function __construct(
        private AdminApiInterface $adminApi,
        private AuditService $auditService,
    ) {}

    public function createMember(CreateMemberRequest $request): CreateMemberResource|ApiErrorResponse
    {
        $data = $request->validated();

        $existing = Member::where('user_id', $data['userId'])->first();
        if ($existing) {
            $operationId = AuditOperation::where('user_id', $data['userId'])
                ->where('operation', 'createMember')
                ->value('id');

            return new CreateMemberResource([
                'memberId' => $existing->id,
                'accountId' => $existing->account->account_id,
                'operationId' => $operationId,
            ]);
        }

        return DB::transaction(function () use ($data) {
            $adminId = Str::uuid()->toString();

            $result = $this->adminApi->createMember($adminId, $data);

            $this->adminApi->setInvestmentProfile($adminId, [
                'allocations' => $data['initialInvestmentProfile'],
            ]);

            $operationId = $this->auditService->startOperation($data['userId'], 'createMember');
            $this->auditService->recordEvent($operationId, 'member_created', [
                'memberId' => $result['memberId'],
                'accountId' => $result['accountId'],
            ]);
            $this->auditService->completeOperation($operationId, 'success');

            return new CreateMemberResource([
                'memberId' => $result['memberId'],
                'accountId' => $result['accountId'],
                'operationId' => $operationId,
            ]);
        });
    }
}
