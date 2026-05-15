<?php

namespace Tests\Unit;

use App\Contracts\AdminApiInterface;
use App\Models\Holding;
use App\Models\Transaction;
use App\Models\UnitPrice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class HoldingsCalculationTest extends TestCase
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

    public function test_units_equals_previous_plus_cashflow_divided_by_price(): void
    {
        $member = $this->createMemberWithProfile('user-units', [
            ['assetCode' => 'Growth', 'percentage' => 100],
        ]);

        $account = DB::table('accounts')->where('account_id', $member['accountId'])->first();

        Transaction::create([
            'id' => 'txn-units-001',
            'account_id' => $account->id,
            'type' => 'contribution',
            'amount' => '200.00',
            'effective_date' => '2024-01-02',
        ]);

        Transaction::create([
            'id' => 'txn-units-002',
            'account_id' => $account->id,
            'type' => 'contribution',
            'amount' => '100.00',
            'effective_date' => '2024-01-03',
        ]);

        UnitPrice::updateOrCreate(['asset_code' => 'Growth', 'date' => '2024-01-02'], ['price' => '2.000000']);
        UnitPrice::updateOrCreate(['asset_code' => 'Growth', 'date' => '2024-01-03'], ['price' => '2.000000']);

        $this->postJson('/mock/moveDayForward', ['days' => 2]);

        $holdingDay2 = Holding::where('account_id', $account->id)
            ->where('effective_date', '2024-01-02')
            ->where('asset_code', 'Growth')
            ->first();

        $holdingDay3 = Holding::where('account_id', $account->id)
            ->where('effective_date', '2024-01-03')
            ->where('asset_code', 'Growth')
            ->first();

        // Day 2: previous=0, cashflow=200, price=2 → units = 0 + 200/2 = 100
        $this->assertEquals(100.0, (float) $holdingDay2->units);

        // Day 3: previous=100, cashflow=100, price=2 → units = 100 + 100/2 = 150
        $expectedUnits = (float) $holdingDay2->units + 100.00 / 2.0;
        $this->assertEquals($expectedUnits, (float) $holdingDay3->units, 'new_units = previous_units + cashflow / price');
    }

    public function test_balance_equals_units_times_price_with_rounding(): void
    {
        $member = $this->createMemberWithProfile('user-bal', [
            ['assetCode' => 'Balanced', 'percentage' => 100],
        ]);

        $account = DB::table('accounts')->where('account_id', $member['accountId'])->first();

        Transaction::create([
            'id' => 'txn-bal-001',
            'account_id' => $account->id,
            'type' => 'contribution',
            'amount' => '100.00',
            'effective_date' => '2024-01-02',
        ]);

        // Use a price that creates fractional units
        UnitPrice::updateOrCreate(['asset_code' => 'Balanced', 'date' => '2024-01-02'], ['price' => '3.000000']);

        $this->postJson('/mock/moveDayForward', ['days' => 1]);

        $holding = Holding::where('account_id', $account->id)
            ->where('effective_date', '2024-01-02')
            ->where('asset_code', 'Balanced')
            ->first();

        $this->assertNotNull($holding);

        // balance = round(units * unit_price, 2)
        $expectedBalance = round((float) $holding->units * (float) $holding->unit_price, 2);
        $this->assertEquals($expectedBalance, (float) $holding->balance, 'balance = round(units * unitPrice, 2)');
    }

    public function test_carry_forward_zero_cashflow_units_unchanged(): void
    {
        $member = $this->createMemberWithProfile('user-carry', [
            ['assetCode' => 'Cash', 'percentage' => 40],
            ['assetCode' => 'Growth', 'percentage' => 60],
        ]);

        $account = DB::table('accounts')->where('account_id', $member['accountId'])->first();

        Transaction::create([
            'id' => 'txn-carry-001',
            'account_id' => $account->id,
            'type' => 'contribution',
            'amount' => '1000.00',
            'effective_date' => '2024-01-02',
        ]);

        UnitPrice::updateOrCreate(['asset_code' => 'Cash', 'date' => '2024-01-02'], ['price' => '1.000000']);
        UnitPrice::updateOrCreate(['asset_code' => 'Growth', 'date' => '2024-01-02'], ['price' => '2.000000']);
        UnitPrice::updateOrCreate(['asset_code' => 'Cash', 'date' => '2024-01-03'], ['price' => '1.000000']);
        UnitPrice::updateOrCreate(['asset_code' => 'Growth', 'date' => '2024-01-03'], ['price' => '3.000000']);

        $this->postJson('/mock/moveDayForward', ['days' => 2]);

        $holdingsDay2 = Holding::where('account_id', $account->id)
            ->where('effective_date', '2024-01-02')
            ->pluck('units', 'asset_code');

        $holdingsDay3 = Holding::where('account_id', $account->id)
            ->where('effective_date', '2024-01-03')
            ->get();

        foreach ($holdingsDay3 as $h3) {
            $prevUnits = (float) $holdingsDay2[$h3->asset_code];
            $this->assertEquals(
                $prevUnits,
                (float) $h3->units,
                "Carry-forward: units for {$h3->asset_code} should be unchanged with zero cash flow"
            );
            // But balance should be recalculated at new price
            $expectedBalance = round($prevUnits * (float) $h3->unit_price, 2);
            $this->assertEquals($expectedBalance, (float) $h3->balance);
        }
    }
}
