<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddTransactionsRequest;
use App\Http\Requests\ResetSubjectStateRequest;
use App\Http\Requests\SetDailyUnitPricesRequest;
use App\Http\Resources\ApiErrorResponse;
use App\Models\Member;
use App\Models\Transaction;
use App\Models\UnitPrice;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class MockControlController extends Controller
{
    public function addTransactions(AddTransactionsRequest $request): JsonResponse
    {
        $data = $request->validated();

        $member = Member::where('user_id', $data['userId'])->first();
        if (! $member) {
            return ApiErrorResponse::make('Member not found');
        }

        $account = $member->account;
        if (! $account || $account->account_id !== $data['accountId']) {
            return ApiErrorResponse::make('Account not found');
        }

        $count = 0;
        foreach ($data['transactions'] as $txn) {
            Transaction::create([
                'id' => Str::uuid()->toString(),
                'account_id' => $account->id,
                'type' => $txn['type'],
                'amount' => $txn['amount'],
                'effective_date' => $txn['effectiveDate'],
            ]);
            $count++;
        }

        return new JsonResponse(['ok' => true, 'addedCount' => $count]);
    }

    public function setDailyUnitPrices(SetDailyUnitPricesRequest $request): JsonResponse
    {
        $data = $request->validated();

        foreach ($data['prices'] as $entry) {
            UnitPrice::updateOrCreate(
                [
                    'asset_code' => $entry['assetCode'],
                    'date' => $data['date'],
                ],
                [
                    'price' => $entry['unitPrice'],
                ]
            );
        }

        return new JsonResponse(['ok' => true]);
    }

    public function resetSubjectState(ResetSubjectStateRequest $request): JsonResponse
    {
        $data = $request->validated();

        Member::where('user_id', $data['userId'])->delete();

        return new JsonResponse(['ok' => true]);
    }
}
