<?php

namespace App\Services;

use App\Contracts\AdminApiInterface;
use App\Models\Account;
use App\Models\Holding;
use App\Models\InvestmentProfile;
use App\Models\Member;
use App\Models\Transaction;
use Illuminate\Support\Str;

class MockAdminApi implements AdminApiInterface
{
    public function createMember(string $adminId, array $data): array
    {
        $member = Member::create([
            'id' => Str::uuid()->toString(),
            'user_id' => $data['userId'] ?? null,
            'admin_id' => $adminId,
            'first_name' => $data['firstName'] ?? null,
            'last_name' => $data['lastName'] ?? null,
            'date_of_birth' => $data['dateOfBirth'] ?? null,
            'email' => $data['email'] ?? null,
            'mobile' => $data['mobile'] ?? null,
            'preferred_name' => $data['preferredName'] ?? null,
            'residential_address' => $data['residentialAddress'] ?? null,
            'postal_address' => $data['postalAddress'] ?? null,
        ]);

        $account = Account::create([
            'id' => Str::uuid()->toString(),
            'member_id' => $member->id,
            'account_id' => Str::uuid()->toString(),
        ]);

        return [
            'adminId' => $member->admin_id,
            'memberId' => $member->id,
            'accountId' => $account->account_id,
        ];
    }

    public function updateMember(string $adminId, array $data): array
    {
        $member = Member::where('admin_id', $adminId)->first();

        if (! $member) {
            throw new \RuntimeException("Member not found for adminId: {$adminId}");
        }

        $updatable = [];
        if (array_key_exists('email', $data)) {
            $updatable['email'] = $data['email'];
        }
        if (array_key_exists('mobile', $data)) {
            $updatable['mobile'] = $data['mobile'];
        }
        if (array_key_exists('preferredName', $data)) {
            $updatable['preferred_name'] = $data['preferredName'];
        }
        if (array_key_exists('residentialAddress', $data)) {
            $updatable['residential_address'] = $data['residentialAddress'];
        }
        if (array_key_exists('postalAddress', $data)) {
            $updatable['postal_address'] = $data['postalAddress'];
        }

        if (! empty($updatable)) {
            $member->update($updatable);
        }

        return [
            'adminId' => $member->admin_id,
            'memberId' => $member->id,
            'email' => $member->email,
            'mobile' => $member->mobile,
            'preferredName' => $member->preferred_name,
            'residentialAddress' => $member->residential_address,
            'postalAddress' => $member->postal_address,
        ];
    }

    public function setInvestmentProfile(string $adminId, array $allocations): array
    {
        $member = Member::where('admin_id', $adminId)->first();

        if (! $member) {
            throw new \RuntimeException("Member not found for adminId: {$adminId}");
        }

        $account = $member->account;

        InvestmentProfile::where('account_id', $account->id)
            ->where('is_current', true)
            ->update(['is_current' => false]);

        $effectiveFrom = $allocations['effectiveFrom'] ?? now()->toDateString();
        $profiles = [];

        foreach ($allocations['allocations'] as $allocation) {
            $profiles[] = InvestmentProfile::create([
                'account_id' => $account->id,
                'asset_code' => $allocation['assetCode'],
                'percentage' => $allocation['percentage'],
                'is_current' => true,
                'effective_from' => $effectiveFrom,
            ]);
        }

        return [
            'accountId' => $account->account_id,
            'effectiveFrom' => $effectiveFrom,
            'allocations' => array_map(fn ($p) => [
                'assetCode' => $p->asset_code,
                'percentage' => $p->percentage,
            ], $profiles),
        ];
    }

    public function getInvestmentPortfolio(string $adminId): array
    {
        $member = Member::where('admin_id', $adminId)->first();

        if (! $member) {
            throw new \RuntimeException("Member not found for adminId: {$adminId}");
        }

        $account = $member->account;

        $profiles = InvestmentProfile::where('account_id', $account->id)
            ->where('is_current', true)
            ->get();

        return [
            'accountId' => $account->account_id,
            'allocations' => $profiles->map(fn ($p) => [
                'assetCode' => $p->asset_code,
                'percentage' => $p->percentage,
            ])->toArray(),
        ];
    }

    public function getTransactionHistory(string $adminId, ?string $fromDate = null, ?string $toDate = null): array
    {
        $member = Member::where('admin_id', $adminId)->first();

        if (! $member) {
            throw new \RuntimeException("Member not found for adminId: {$adminId}");
        }

        $account = $member->account;

        $query = Transaction::where('account_id', $account->id)
            ->orderBy('effective_date', 'asc')
            ->orderBy('id', 'asc');

        if ($fromDate) {
            $query->where('effective_date', '>=', $fromDate);
        }

        if ($toDate) {
            $query->where('effective_date', '<=', $toDate);
        }

        $transactions = $query->get();

        return [
            'accountId' => $account->account_id,
            'transactions' => $transactions->map(fn ($t) => [
                'transactionId' => $t->id,
                'type' => $t->type,
                'amount' => $t->amount,
                'effectiveDate' => $t->effective_date->toDateString(),
            ])->values()->toArray(),
        ];
    }

    public function getHoldings(string $adminId, ?string $asOfDate = null): array
    {
        $member = Member::where('admin_id', $adminId)->first();

        if (! $member) {
            throw new \RuntimeException("Member not found for adminId: {$adminId}");
        }

        $account = $member->account;

        if ($asOfDate) {
            $date = $asOfDate;
        } else {
            $latestHolding = Holding::where('account_id', $account->id)
                ->orderBy('effective_date', 'desc')
                ->first();

            if (! $latestHolding) {
                return [
                    'accountId' => $account->account_id,
                    'asOfDate' => null,
                    'holdings' => [],
                ];
            }

            $date = $latestHolding->effective_date->toDateString();
        }

        $holdings = Holding::where('account_id', $account->id)
            ->where('effective_date', $date)
            ->get();

        return [
            'accountId' => $account->account_id,
            'asOfDate' => $date,
            'holdings' => $holdings->map(fn ($h) => [
                'assetCode' => $h->asset_code,
                'units' => $h->units,
                'unitPrice' => $h->unit_price,
                'balance' => $h->balance,
                'effectiveDate' => $h->effective_date->toDateString(),
            ])->toArray(),
        ];
    }
}
