<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddTransactionsRequest;
use App\Http\Requests\MoveDayForwardRequest;
use App\Http\Requests\ResetSubjectStateRequest;
use App\Http\Requests\SetDailyUnitPricesRequest;
use App\Http\Resources\ApiErrorResponse;
use App\Models\Holding;
use App\Models\InvestmentProfile;
use App\Models\Member;
use App\Models\Transaction;
use App\Models\UnitPrice;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
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

    public function moveDayForward(MoveDayForwardRequest $request): JsonResponse
    {
        $data = $request->validated();
        $days = $data['days'] ?? 1;

        $processedDates = [];

        for ($i = 0; $i < $days; $i++) {
            $systemState = DB::table('system_state')->where('id', 1)->first();
            $currentDate = Carbon::parse($systemState->current_date);
            $processingDate = $currentDate->copy()->addDay();

            $this->processDay($processingDate);

            DB::table('system_state')->where('id', 1)->update([
                'current_date' => $processingDate->toDateString(),
                'updated_at' => now(),
            ]);

            $processedDates[] = $processingDate->toDateString();
        }

        return new JsonResponse(['ok' => true, 'processedDates' => $processedDates]);
    }

    private function processDay(Carbon $processingDate): void
    {
        $dateStr = $processingDate->toDateString();

        $accountsWithHoldings = Holding::select('account_id')
            ->distinct()
            ->pluck('account_id');

        $accountsWithTransactions = Transaction::where('effective_date', $dateStr)
            ->select('account_id')
            ->distinct()
            ->pluck('account_id');

        $eligibleAccountIds = $accountsWithHoldings->merge($accountsWithTransactions)->unique();

        foreach ($eligibleAccountIds as $accountId) {
            $this->processAccountDay($accountId, $dateStr);
        }
    }

    private function processAccountDay(string $accountId, string $dateStr): void
    {
        $profile = InvestmentProfile::where('account_id', $accountId)
            ->where('is_current', true)
            ->orderBy('asset_code')
            ->get();

        if ($profile->isEmpty()) {
            return;
        }

        $netCashFlow = (float) Transaction::where('account_id', $accountId)
            ->where('effective_date', $dateStr)
            ->sum('amount');

        $allocations = $this->allocateCashFlow($netCashFlow, $profile);

        foreach ($profile as $index => $allocation) {
            $assetCode = $allocation->asset_code;
            $allocatedAmount = $allocations[$index];

            $unitPrice = $this->getUnitPrice($assetCode, $dateStr);

            $previousUnits = $this->getPreviousUnits($accountId, $assetCode);

            $newUnits = $previousUnits + ($unitPrice != 0 ? $allocatedAmount / $unitPrice : 0);

            $balance = round($newUnits * $unitPrice, 2);

            Holding::create([
                'account_id' => $accountId,
                'asset_code' => $assetCode,
                'units' => $newUnits,
                'unit_price' => $unitPrice,
                'balance' => $balance,
                'effective_date' => $dateStr,
            ]);
        }
    }

    private function allocateCashFlow(float $netCashFlow, $profile): array
    {
        $allocations = [];
        $sum = 0.0;
        $count = $profile->count();

        for ($i = 0; $i < $count - 1; $i++) {
            $raw = $netCashFlow * (float) $profile[$i]->percentage / 100;
            $amount = floor($raw * 100) / 100;
            $allocations[] = $amount;
            $sum += $amount;
        }

        $allocations[] = round($netCashFlow - $sum, 2);

        return $allocations;
    }

    private function getUnitPrice(string $assetCode, string $dateStr): float
    {
        $price = UnitPrice::where('asset_code', $assetCode)
            ->where('date', '<=', $dateStr)
            ->orderByDesc('date')
            ->first();

        return $price ? (float) $price->price : 1.0;
    }

    private function getPreviousUnits(string $accountId, string $assetCode): float
    {
        $holding = Holding::where('account_id', $accountId)
            ->where('asset_code', $assetCode)
            ->orderByDesc('effective_date')
            ->first();

        return $holding ? (float) $holding->units : 0.0;
    }
}
