<?php

namespace Tests\Unit;

use App\Contracts\AdminApiInterface;
use App\Models\Holding;
use App\Models\Transaction;
use App\Models\UnitPrice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DeterminismTest extends TestCase
{
    use RefreshDatabase;

    private function seedSystemState(): void
    {
        DB::table('system_state')->updateOrInsert(
            ['id' => 1],
            ['current_date' => '2024-01-01', 'created_at' => now(), 'updated_at' => now()]
        );
    }

    private function runScenario(): array
    {
        $api = app(AdminApiInterface::class);
        $member = $api->createMember('admin-det-001', [
            'userId' => 'user-det',
            'email' => 'det@example.com',
        ]);

        $api->setInvestmentProfile('admin-det-001', [
            'allocations' => [
                ['assetCode' => 'Cash', 'percentage' => 30],
                ['assetCode' => 'Conservative', 'percentage' => 30],
                ['assetCode' => 'Growth', 'percentage' => 40],
            ],
        ]);

        $account = DB::table('accounts')->where('account_id', $member['accountId'])->first();

        Transaction::create([
            'id' => 'txn-det-001',
            'account_id' => $account->id,
            'type' => 'contribution',
            'amount' => '1500.00',
            'effective_date' => '2024-01-02',
        ]);

        Transaction::create([
            'id' => 'txn-det-002',
            'account_id' => $account->id,
            'type' => 'contribution',
            'amount' => '300.00',
            'effective_date' => '2024-01-03',
        ]);

        UnitPrice::updateOrCreate(['asset_code' => 'Cash', 'date' => '2024-01-02'], ['price' => '1.000000']);
        UnitPrice::updateOrCreate(['asset_code' => 'Conservative', 'date' => '2024-01-02'], ['price' => '1.250000']);
        UnitPrice::updateOrCreate(['asset_code' => 'Growth', 'date' => '2024-01-02'], ['price' => '2.500000']);
        UnitPrice::updateOrCreate(['asset_code' => 'Cash', 'date' => '2024-01-03'], ['price' => '1.000000']);
        UnitPrice::updateOrCreate(['asset_code' => 'Conservative', 'date' => '2024-01-03'], ['price' => '1.300000']);
        UnitPrice::updateOrCreate(['asset_code' => 'Growth', 'date' => '2024-01-03'], ['price' => '2.600000']);

        $this->postJson('/mock/moveDayForward', ['days' => 2]);

        return Holding::where('account_id', $account->id)
            ->orderBy('effective_date')
            ->orderBy('asset_code')
            ->get()
            ->map(fn ($h) => [
                'asset_code' => $h->asset_code,
                'effective_date' => $h->effective_date->toDateString(),
                'units' => (string) $h->units,
                'unit_price' => (string) $h->unit_price,
                'balance' => (string) $h->balance,
            ])
            ->toArray();
    }

    public function test_same_inputs_produce_identical_holdings(): void
    {
        // Run 1
        $this->seedSystemState();
        $run1 = $this->runScenario();

        // Clean all tables (using DELETE to avoid implicit commit from TRUNCATE)
        DB::table('holdings')->delete();
        DB::table('transactions')->delete();
        DB::table('unit_prices')->delete();
        DB::table('investment_profiles')->delete();
        DB::table('accounts')->delete();
        DB::table('members')->delete();
        DB::table('system_state')->delete();

        // Run 2
        $this->seedSystemState();
        $run2 = $this->runScenario();

        $this->assertEquals($run1, $run2, 'Identical inputs must produce identical holdings');
    }
}
