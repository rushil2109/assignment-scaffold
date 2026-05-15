<?php

namespace App\Http\Controllers;

use App\Contracts\AdminApiInterface;
use App\Http\Requests\CreateMemberRequest;
use App\Http\Requests\GetHoldingsRequest;
use App\Http\Requests\GetInvestmentPortfolioRequest;
use App\Http\Requests\GetTransactionHistoryRequest;
use App\Http\Requests\SetInvestmentProfileRequest;
use App\Http\Requests\UpdateMemberRequest;
use App\Http\Resources\ApiErrorResponse;
use App\Http\Resources\CreateMemberResource;
use App\Models\AuditOperation;
use App\Models\Member;
use App\Services\AuditService;
use App\Traits\ResolvesMember;
use Illuminate\Http\JsonResponse;
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

    public function updateMember(UpdateMemberRequest $request): JsonResponse
    {
        $data = $request->validated();

        $adminId = $this->resolveAdminId($data['userId'], $data['memberId']);

        if (! $adminId) {
            return ApiErrorResponse::make('Member not found.');
        }

        $updatable = array_diff_key($data, array_flip(['userId', 'memberId']));

        return DB::transaction(function () use ($data, $adminId, $updatable) {
            $this->adminApi->updateMember($adminId, $updatable);

            $operationId = $this->auditService->startOperation($data['userId'], 'updateMember');
            $this->auditService->recordEvent($operationId, 'member_updated', $updatable);
            $this->auditService->completeOperation($operationId, 'success');

            return new JsonResponse(['ok' => true, 'operationId' => $operationId]);
        });
    }

    public function setInvestmentProfile(SetInvestmentProfileRequest $request): JsonResponse
    {
        $data = $request->validated();

        $adminId = $this->resolveAdminId($data['userId'], $data['memberId'], $data['accountId']);

        if (! $adminId) {
            return ApiErrorResponse::make('Member not found.');
        }

        return DB::transaction(function () use ($data, $adminId) {
            $this->adminApi->setInvestmentProfile($adminId, [
                'allocations' => $data['allocations'],
            ]);

            $operationId = $this->auditService->startOperation($data['userId'], 'setInvestmentProfile');
            $this->auditService->recordEvent($operationId, 'profile_updated', [
                'allocations' => $data['allocations'],
            ]);
            $this->auditService->completeOperation($operationId, 'success');

            return new JsonResponse(['ok' => true, 'operationId' => $operationId]);
        });
    }

    public function getInvestmentPortfolio(GetInvestmentPortfolioRequest $request): JsonResponse
    {
        $data = $request->validated();

        $adminId = $this->resolveAdminId($data['userId'], $data['memberId'], $data['accountId']);

        if (! $adminId) {
            return ApiErrorResponse::make('Member not found.');
        }

        $result = $this->adminApi->getInvestmentPortfolio($adminId);

        return new JsonResponse([
            'ok' => true,
            'allocations' => $result['allocations'],
        ]);
    }

    public function getTransactionHistory(GetTransactionHistoryRequest $request): JsonResponse
    {
        $data = $request->validated();

        $adminId = $this->resolveAdminId($data['userId'], $data['memberId'], $data['accountId']);

        if (! $adminId) {
            return ApiErrorResponse::make('Member not found.');
        }

        $result = $this->adminApi->getTransactionHistory(
            $adminId,
            $data['fromDate'] ?? null,
            $data['toDate'] ?? null,
        );

        return new JsonResponse([
            'ok' => true,
            'transactions' => $result['transactions'],
        ]);
    }

    public function getHoldings(GetHoldingsRequest $request): JsonResponse
    {
        $data = $request->validated();

        $adminId = $this->resolveAdminId($data['userId'], $data['memberId'], $data['accountId']);

        if (! $adminId) {
            return ApiErrorResponse::make('Member not found.');
        }

        $result = $this->adminApi->getHoldings(
            $adminId,
            $data['asOfDate'] ?? null,
        );

        return new JsonResponse([
            'ok' => true,
            'holdings' => $result['holdings'],
        ]);
    }
}
