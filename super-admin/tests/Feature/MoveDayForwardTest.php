<?php

namespace Tests\Feature;

use App\Contracts\AdminApiInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class MoveDayForwardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        DB::table('system_state')->insert([
            'id' => 1,
            'current_date' => '2024-01-01',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createMemberWithProfile(string $userId = 'user-001'): array
    {
        $api = app(AdminApiInterface::class);
        $member = $api->createMember('admin-' . $userId, [
            'userId' => $userId,
            'email' => $userId . '@example.com',
        ]);

        $api->setInvestmentProfile('admin-' . $userId, [
            'allocations' => [
                ['assetCode' => 'Cash', 'percentage' => 40],
                ['assetCode' => 'Growth', 'percentage' => 60],
            ],
        ]);

        return $member;
    }

    public function test_single_day_advancement_with_one_transaction(): void
    {
        $member = $this->createMemberWithProfile();

        $this->postJson('/mock/addTransactions', [
            'userId' => 'user-001',
            'accountId' => $member['accountId'],
            'transactions' => [
                ['type' => 'contribution', 'amount' => '1000.00', 'effectiveDate' => '2024-01-02'],
            ],
        ]);

        $this->postJson('/mock/setDailyUnitPrices', [
            'date' => '2024-01-02',
            'prices' => [
                ['assetCode' => 'Cash', 'unitPrice' => '1.000000'],
                ['assetCode' => 'Growth', 'unitPrice' => '2.000000'],
            ],
        ]);

        $response = $this->postJson('/mock/moveDayForward', ['days' => 1]);

        $response->assertOk();
        $response->assertJson([
            'ok' => true,
            'processedDates' => ['2024-01-02'],
        ]);

        // Cash: floor(1000 * 40 / 100, 2dp) = 400.00, units = 400/1 = 400
        $this->assertDatabaseHas('holdings', [
            'asset_code' => 'Cash',
            'effective_date' => '2024-01-02',
            'units' => '400.000000',
            'unit_price' => '1.000000',
            'balance' => '400.00',
        ]);

        // Growth: remainder = 1000 - 400 = 600.00, units = 600/2 = 300
        $this->assertDatabaseHas('holdings', [
            'asset_code' => 'Growth',
            'effective_date' => '2024-01-02',
            'units' => '300.000000',
            'unit_price' => '2.000000',
            'balance' => '600.00',
        ]);

        $this->assertDatabaseHas('system_state', [
            'id' => 1,
            'current_date' => '2024-01-02',
        ]);
    }

    public function test_multi_day_advancement_sequential_processing(): void
    {
        $member = $this->createMemberWithProfile();

        $this->postJson('/mock/addTransactions', [
            'userId' => 'user-001',
            'accountId' => $member['accountId'],
            'transactions' => [
                ['type' => 'contribution', 'amount' => '500.00', 'effectiveDate' => '2024-01-02'],
                ['type' => 'contribution', 'amount' => '200.00', 'effectiveDate' => '2024-01-03'],
            ],
        ]);

        $this->postJson('/mock/setDailyUnitPrices', [
            'date' => '2024-01-02',
            'prices' => [
                ['assetCode' => 'Cash', 'unitPrice' => '1.000000'],
                ['assetCode' => 'Growth', 'unitPrice' => '1.000000'],
            ],
        ]);

        $this->postJson('/mock/setDailyUnitPrices', [
            'date' => '2024-01-03',
            'prices' => [
                ['assetCode' => 'Cash', 'unitPrice' => '1.000000'],
                ['assetCode' => 'Growth', 'unitPrice' => '1.500000'],
            ],
        ]);

        $response = $this->postJson('/mock/moveDayForward', ['days' => 2]);

        $response->assertOk();
        $response->assertJson([
            'ok' => true,
            'processedDates' => ['2024-01-02', '2024-01-03'],
        ]);

        // Day 2: Cash gets 200, Growth gets 300 (at price 1.0 each)
        // Day 3: previous Cash units=200, Growth units=300
        //   Cash flow 200: Cash gets 80, Growth gets 120
        //   Cash new_units = 200 + 80/1.0 = 280, Growth new_units = 300 + 120/1.5 = 380
        $this->assertDatabaseHas('holdings', [
            'asset_code' => 'Cash',
            'effective_date' => '2024-01-03',
            'units' => '280.000000',
        ]);

        $this->assertDatabaseHas('holdings', [
            'asset_code' => 'Growth',
            'effective_date' => '2024-01-03',
            'unit_price' => '1.500000',
        ]);

        $this->assertDatabaseHas('system_state', [
            'id' => 1,
            'current_date' => '2024-01-03',
        ]);
    }

    public function test_carry_forward_no_transactions_day(): void
    {
        $member = $this->createMemberWithProfile();

        $this->postJson('/mock/addTransactions', [
            'userId' => 'user-001',
            'accountId' => $member['accountId'],
            'transactions' => [
                ['type' => 'contribution', 'amount' => '1000.00', 'effectiveDate' => '2024-01-02'],
            ],
        ]);

        $this->postJson('/mock/setDailyUnitPrices', [
            'date' => '2024-01-02',
            'prices' => [
                ['assetCode' => 'Cash', 'unitPrice' => '1.000000'],
                ['assetCode' => 'Growth', 'unitPrice' => '2.000000'],
            ],
        ]);

        $this->postJson('/mock/setDailyUnitPrices', [
            'date' => '2024-01-03',
            'prices' => [
                ['assetCode' => 'Cash', 'unitPrice' => '1.000000'],
                ['assetCode' => 'Growth', 'unitPrice' => '3.000000'],
            ],
        ]);

        $this->postJson('/mock/moveDayForward', ['days' => 2]);

        // Day 3: no transactions, so units carry forward but balance recalculated
        // Growth had 300 units from day 2, now at price 3.0 → balance = 900
        $this->assertDatabaseHas('holdings', [
            'asset_code' => 'Growth',
            'effective_date' => '2024-01-03',
            'units' => '300.000000',
            'unit_price' => '3.000000',
            'balance' => '900.00',
        ]);

        // Cash had 400 units, price unchanged at 1.0 → balance = 400
        $this->assertDatabaseHas('holdings', [
            'asset_code' => 'Cash',
            'effective_date' => '2024-01-03',
            'units' => '400.000000',
            'unit_price' => '1.000000',
            'balance' => '400.00',
        ]);
    }

    public function test_double_call_guard_no_duplicate_holdings(): void
    {
        $member = $this->createMemberWithProfile();

        $this->postJson('/mock/addTransactions', [
            'userId' => 'user-001',
            'accountId' => $member['accountId'],
            'transactions' => [
                ['type' => 'contribution', 'amount' => '100.00', 'effectiveDate' => '2024-01-02'],
            ],
        ]);

        $this->postJson('/mock/setDailyUnitPrices', [
            'date' => '2024-01-02',
            'prices' => [
                ['assetCode' => 'Cash', 'unitPrice' => '1.000000'],
                ['assetCode' => 'Growth', 'unitPrice' => '1.000000'],
            ],
        ]);

        $response1 = $this->postJson('/mock/moveDayForward', ['days' => 1]);
        $response1->assertJson(['ok' => true, 'processedDates' => ['2024-01-02']]);

        // Second call advances to next day (2024-01-03) — not the same day
        $response2 = $this->postJson('/mock/moveDayForward', ['days' => 1]);
        $response2->assertOk();
        $response2->assertJson(['ok' => true, 'processedDates' => ['2024-01-03']]);

        // Verify no duplicate holdings for 2024-01-02
        $holdingsDay2 = DB::table('holdings')
            ->where('effective_date', '2024-01-02')
            ->count();
        $this->assertEquals(2, $holdingsDay2);
    }

    public function test_rounding_with_fractional_percentages(): void
    {
        $api = app(AdminApiInterface::class);
        $member = $api->createMember('admin-round-001', [
            'userId' => 'user-round',
            'email' => 'round@example.com',
        ]);

        $api->setInvestmentProfile('admin-round-001', [
            'allocations' => [
                ['assetCode' => 'Balanced', 'percentage' => 33.33],
                ['assetCode' => 'Conservative', 'percentage' => 33.33],
                ['assetCode' => 'Growth', 'percentage' => 33.34],
            ],
        ]);

        $this->postJson('/mock/addTransactions', [
            'userId' => 'user-round',
            'accountId' => $member['accountId'],
            'transactions' => [
                ['type' => 'contribution', 'amount' => '100.00', 'effectiveDate' => '2024-01-02'],
            ],
        ]);

        $this->postJson('/mock/setDailyUnitPrices', [
            'date' => '2024-01-02',
            'prices' => [
                ['assetCode' => 'Balanced', 'unitPrice' => '1.000000'],
                ['assetCode' => 'Conservative', 'unitPrice' => '1.000000'],
                ['assetCode' => 'Growth', 'unitPrice' => '1.000000'],
            ],
        ]);

        $this->postJson('/mock/moveDayForward', ['days' => 1]);

        // Balanced: floor(100 * 33.33 / 100, 2dp) = floor(33.33) = 33.33
        // Conservative: floor(100 * 33.33 / 100, 2dp) = 33.33
        // Growth (last): 100 - 33.33 - 33.33 = 33.34
        // Sum: 33.33 + 33.33 + 33.34 = 100.00 ✓

        $holdings = DB::table('holdings')
            ->where('effective_date', '2024-01-02')
            ->orderBy('asset_code')
            ->get();

        $totalBalance = $holdings->sum('balance');
        $this->assertEquals(100.00, $totalBalance);
    }
}
