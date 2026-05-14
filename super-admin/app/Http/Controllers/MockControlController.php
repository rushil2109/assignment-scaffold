<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\Transaction;
use App\Models\UnitPrice;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MockControlController extends Controller
{
    public function addTransactions(Request $request): JsonResponse
    {
        $userId = $request->input('userId');
        $accountId = $request->input('accountId');

        $member = Member::where('user_id', $userId)->first();

        if (! $member) {
            return new JsonResponse(['ok' => false, 'error' => 'Member not found']);
        }

        $account = $member->account;

        if (! $account || $account->account_id !== $accountId) {
            return new JsonResponse(['ok' => false, 'error' => 'Account not found']);
        }

        $transactions = $request->input('transactions', []);
        $count = 0;

        foreach ($transactions as $txn) {
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

    public function setDailyUnitPrices(Request $request): JsonResponse
    {
        $date = $request->input('date');
        $prices = $request->input('prices', []);

        foreach ($prices as $entry) {
            UnitPrice::updateOrCreate(
                [
                    'asset_code' => $entry['assetCode'],
                    'date' => $date,
                ],
                [
                    'price' => $entry['unitPrice'],
                ]
            );
        }

        return new JsonResponse(['ok' => true]);
    }
}
