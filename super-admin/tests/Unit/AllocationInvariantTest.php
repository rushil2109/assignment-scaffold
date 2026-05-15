<?php

namespace Tests\Unit;

use App\Contracts\AdminApiInterface;
use App\Models\Holding;
use App\Models\Transaction;
use App\Models\UnitPrice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AllocationInvariantTest extends TestCase
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

    private function createMemberWithProfile(string $userId, array $allocations): array
    {
        $api = app(AdminApiInterface::class);
        $member = $api->createMember('admin-'.$userId, [
            'userId' => $userId,
            'email' => $userId.'@example.com',
        ]);

        $api->setInvestmentProfile('admin-'.$userId, [
            'allocations' => $allocations,
        ]);

        return $member;
    }

    private function advanceDay(): void
    {
        $this->postJson('/mock/moveDayForward', ['days' => 1]);
    }

    public function test_fractional_split_sums_to_cash_flow(): void
    {
        $member = $this->createMemberWithProfile('user-frac', [
            ['assetCode' => 'Balanced', 'percentage' => 33.33],
            ['assetCode' => 'Conservative', 'percentage' => 33.33],
            ['assetCode' => 'Growth', 'percentage' => 33.34],
        ]);

        $account = DB::table('accounts')->where('account_id', $member['accountId'])->first();

        Transaction::create([
            'id' => 'txn-frac-001',
            'account_id' => $account->id,
            'type' => 'contribution',
            'amount' => '1000.00',
            'effective_date' => '2024-01-02',
        ]);

        UnitPrice::updateOrCreate(['asset_code' => 'Balanced', 'date' => '2024-01-02'], ['price' => '1.000000']);
        UnitPrice::updateOrCreate(['asset_code' => 'Conservative', 'date' => '2024-01-02'], ['price' => '1.000000']);
        UnitPrice::updateOrCreate(['asset_code' => 'Growth', 'date' => '2024-01-02'], ['price' => '1.000000']);

        $this->advanceDay();

        $holdings = Holding::where('account_id', $account->id)
            ->where('effective_date', '2024-01-02')
            ->get();

        $totalBalance = $holdings->sum('balance');
        $this->assertEquals(1000.00, $totalBalance, 'Fractional allocations must sum to exact cash flow');
    }

    public function test_single_asset_100_percent_allocation(): void
    {
        $member = $this->createMemberWithProfile('user-single', [
            ['assetCode' => 'Cash', 'percentage' => 100],
        ]);

        $account = DB::table('accounts')->where('account_id', $member['accountId'])->first();

        Transaction::create([
            'id' => 'txn-single-001',
            'account_id' => $account->id,
            'type' => 'contribution',
            'amount' => '750.50',
            'effective_date' => '2024-01-02',
        ]);

        UnitPrice::updateOrCreate(['asset_code' => 'Cash', 'date' => '2024-01-02'], ['price' => '1.000000']);

        $this->advanceDay();

        $holding = Holding::where('account_id', $account->id)
            ->where('effective_date', '2024-01-02')
            ->where('asset_code', 'Cash')
            ->first();

        $this->assertNotNull($holding);
        $this->assertEquals(750.50, (float) $holding->balance, 'Single 100% asset must receive entire cash flow');
    }

    public function test_zero_cash_flow_produces_zero_allocations(): void
    {
        $member = $this->createMemberWithProfile('user-zero', [
            ['assetCode' => 'Cash', 'percentage' => 50],
            ['assetCode' => 'Growth', 'percentage' => 50],
        ]);

        $account = DB::table('accounts')->where('account_id', $member['accountId'])->first();

        // Add a transaction on day 2 to make the account eligible, then test day 3 with no transactions
        Transaction::create([
            'id' => 'txn-zero-001',
            'account_id' => $account->id,
            'type' => 'contribution',
            'amount' => '100.00',
            'effective_date' => '2024-01-02',
        ]);

        UnitPrice::updateOrCreate(['asset_code' => 'Cash', 'date' => '2024-01-02'], ['price' => '1.000000']);
        UnitPrice::updateOrCreate(['asset_code' => 'Growth', 'date' => '2024-01-02'], ['price' => '1.000000']);
        UnitPrice::updateOrCreate(['asset_code' => 'Cash', 'date' => '2024-01-03'], ['price' => '1.000000']);
        UnitPrice::updateOrCreate(['asset_code' => 'Growth', 'date' => '2024-01-03'], ['price' => '1.000000']);

        // Advance 2 days: day 2 has a transaction, day 3 has zero cash flow
        $this->postJson('/mock/moveDayForward', ['days' => 2]);

        $holdingsDay2 = Holding::where('account_id', $account->id)
            ->where('effective_date', '2024-01-02')
            ->get();
        $holdingsDay3 = Holding::where('account_id', $account->id)
            ->where('effective_date', '2024-01-03')
            ->get();

        // Day 3 units should equal day 2 units (zero new allocation)
        foreach ($holdingsDay2 as $h2) {
            $h3 = $holdingsDay3->firstWhere('asset_code', $h2->asset_code);
            $this->assertNotNull($h3);
            $this->assertEquals(
                (float) $h2->units,
                (float) $h3->units,
                "Zero cash flow should not change units for {$h2->asset_code}"
            );
        }
    }
}
