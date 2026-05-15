<?php

namespace Tests\Unit;

use App\Contracts\AdminApiInterface;
use App\Models\Holding;
use App\Models\Transaction;
use App\Models\UnitPrice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ProfileChangeIsolationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        DB::table('system_state')->updateOrInsert(
            ['id' => 1],
            ['current_date' => '2024-01-01', 'created_at' => now(), 'updated_at' => now()]
        );
    }

    public function test_profile_change_does_not_alter_past_holdings(): void
    {
        $api = app(AdminApiInterface::class);
        $member = $api->createMember('admin-iso-001', [
            'userId' => 'user-iso',
            'email' => 'iso@example.com',
        ]);

        $api->setInvestmentProfile('admin-iso-001', [
            'allocations' => [
                ['assetCode' => 'Cash', 'percentage' => 50],
                ['assetCode' => 'Growth', 'percentage' => 50],
            ],
        ]);

        $account = DB::table('accounts')->where('account_id', $member['accountId'])->first();

        // Add transactions for days 2-4
        foreach (['2024-01-02', '2024-01-03', '2024-01-04'] as $i => $date) {
            Transaction::create([
                'id' => 'txn-iso-00'.($i + 1),
                'account_id' => $account->id,
                'type' => 'contribution',
                'amount' => '100.00',
                'effective_date' => $date,
            ]);
        }

        // Set prices for days 2-5
        foreach (['2024-01-02', '2024-01-03', '2024-01-04', '2024-01-05'] as $date) {
            UnitPrice::updateOrCreate(['asset_code' => 'Cash', 'date' => $date], ['price' => '1.000000']);
            UnitPrice::updateOrCreate(['asset_code' => 'Growth', 'date' => $date], ['price' => '2.000000']);
            UnitPrice::updateOrCreate(['asset_code' => 'Balanced', 'date' => $date], ['price' => '1.500000']);
        }

        // Process days 1-3
        $this->postJson('/mock/moveDayForward', ['days' => 3]);

        // Snapshot holdings for days 2-4 before profile change
        $holdingsBefore = Holding::where('account_id', $account->id)
            ->whereIn('effective_date', ['2024-01-02', '2024-01-03', '2024-01-04'])
            ->get()
            ->map(fn ($h) => [
                'asset_code' => $h->asset_code,
                'effective_date' => $h->effective_date->toDateString(),
                'units' => (float) $h->units,
                'balance' => (float) $h->balance,
            ])
            ->toArray();

        // Change profile — now 100% Balanced
        $api->setInvestmentProfile('admin-iso-001', [
            'allocations' => [
                ['assetCode' => 'Balanced', 'percentage' => 100],
            ],
        ]);

        // Process day 4
        $this->postJson('/mock/moveDayForward', ['days' => 1]);

        // Verify days 2-4 holdings are unchanged
        $holdingsAfter = Holding::where('account_id', $account->id)
            ->whereIn('effective_date', ['2024-01-02', '2024-01-03', '2024-01-04'])
            ->get()
            ->map(fn ($h) => [
                'asset_code' => $h->asset_code,
                'effective_date' => $h->effective_date->toDateString(),
                'units' => (float) $h->units,
                'balance' => (float) $h->balance,
            ])
            ->toArray();

        $this->assertEquals($holdingsBefore, $holdingsAfter, 'Past holdings must remain unchanged after profile change');

        // Verify day 5 uses the new profile (Balanced only)
        $holdingsDay5 = Holding::where('account_id', $account->id)
            ->where('effective_date', '2024-01-05')
            ->pluck('asset_code')
            ->toArray();

        $this->assertContains('Balanced', $holdingsDay5, 'Day 5 should use new profile');
    }
}
