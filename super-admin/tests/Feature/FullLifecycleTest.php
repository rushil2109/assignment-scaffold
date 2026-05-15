<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\SeedsSystemState;

class FullLifecycleTest extends TestCase
{
    use RefreshDatabase;
    use SeedsSystemState;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedSystemState('2024-01-01');
    }

    public function test_full_lifecycle_create_to_holdings(): void
    {
        // 1. Create member with profile: 60% Growth, 40% Cash
        $createResponse = $this->postJson('/public/createMember', [
            'userId' => 'user-lifecycle-001',
            'firstName' => 'Lifecycle',
            'lastName' => 'Test',
            'email' => 'lifecycle@example.com',
            'mobile' => '0400000001',
            'dateOfBirth' => '1985-06-15',
            'initialInvestmentProfile' => [
                ['assetCode' => 'Growth', 'percentage' => 60],
                ['assetCode' => 'Cash', 'percentage' => 40],
            ],
        ]);

        $createResponse->assertOk();
        $createResponse->assertJson(['ok' => true]);
        $this->assertArrayHasKey('memberId', $createResponse->json());
        $this->assertArrayHasKey('accountId', $createResponse->json());
        $this->assertArrayHasKey('operationId', $createResponse->json());

        $memberId = $createResponse->json('memberId');
        $accountId = $createResponse->json('accountId');

        // 2. Add transactions
        $this->postJson('/mock/addTransactions', [
            'userId' => 'user-lifecycle-001',
            'accountId' => $accountId,
            'transactions' => [
                ['type' => 'contribution', 'amount' => '1000.00', 'effectiveDate' => '2024-01-02'],
            ],
        ])->assertJson(['ok' => true, 'addedCount' => 1]);

        // 3. Set unit prices for the day
        $this->postJson('/mock/setDailyUnitPrices', [
            'date' => '2024-01-02',
            'prices' => [
                ['assetCode' => 'Growth', 'unitPrice' => '2.000000'],
                ['assetCode' => 'Cash', 'unitPrice' => '1.000000'],
            ],
        ])->assertJson(['ok' => true]);

        // 4. Move day forward to trigger processing
        $moveResponse = $this->postJson('/mock/moveDayForward', ['days' => 1]);
        $moveResponse->assertOk();
        $moveResponse->assertJson([
            'ok' => true,
            'processedDates' => ['2024-01-02'],
        ]);

        // 5. Get holdings and verify exact calculated values
        $holdingsResponse = $this->postJson('/public/getHoldings', [
            'userId' => 'user-lifecycle-001',
            'memberId' => $memberId,
            'accountId' => $accountId,
        ]);

        $holdingsResponse->assertOk();
        $holdingsResponse->assertJson(['ok' => true]);

        $holdings = $holdingsResponse->json('holdings');
        $this->assertCount(2, $holdings);

        // Manual calculation:
        // Total cash flow = 1000.00
        // Cash allocation: floor(1000 * 40/100, 2dp) = 400.00
        // Growth allocation (remainder): 1000 - 400 = 600.00
        // Cash units: 400.00 / 1.00 = 400.000000
        // Growth units: 600.00 / 2.00 = 300.000000
        // Cash balance: 400.000000 * 1.00 = 400.00
        // Growth balance: 300.000000 * 2.00 = 600.00

        $holdingsByAsset = collect($holdings)->keyBy('assetCode');

        $cash = $holdingsByAsset['Cash'];
        $this->assertEquals(400.0, $cash['units']);
        $this->assertEquals(1.0, $cash['unitPrice']);
        $this->assertEquals(400.0, $cash['balance']);
        $this->assertEquals('2024-01-02', $cash['effectiveDate']);

        $growth = $holdingsByAsset['Growth'];
        $this->assertEquals(300.0, $growth['units']);
        $this->assertEquals(2.0, $growth['unitPrice']);
        $this->assertEquals(600.0, $growth['balance']);
        $this->assertEquals('2024-01-02', $growth['effectiveDate']);
    }

    public function test_multi_day_lifecycle_with_accumulation(): void
    {
        $createResponse = $this->postJson('/public/createMember', [
            'userId' => 'user-lifecycle-002',
            'firstName' => 'Multi',
            'lastName' => 'Day',
            'email' => 'multi@example.com',
            'mobile' => '0400000002',
            'dateOfBirth' => '1990-03-20',
            'initialInvestmentProfile' => [
                ['assetCode' => 'Balanced', 'percentage' => 50],
                ['assetCode' => 'Conservative', 'percentage' => 50],
            ],
        ]);

        $memberId = $createResponse->json('memberId');
        $accountId = $createResponse->json('accountId');

        $this->postJson('/mock/addTransactions', [
            'userId' => 'user-lifecycle-002',
            'accountId' => $accountId,
            'transactions' => [
                ['type' => 'contribution', 'amount' => '500.00', 'effectiveDate' => '2024-01-02'],
                ['type' => 'contribution', 'amount' => '300.00', 'effectiveDate' => '2024-01-03'],
            ],
        ]);

        $this->postJson('/mock/setDailyUnitPrices', [
            'date' => '2024-01-02',
            'prices' => [
                ['assetCode' => 'Balanced', 'unitPrice' => '1.000000'],
                ['assetCode' => 'Conservative', 'unitPrice' => '1.000000'],
            ],
        ]);

        $this->postJson('/mock/setDailyUnitPrices', [
            'date' => '2024-01-03',
            'prices' => [
                ['assetCode' => 'Balanced', 'unitPrice' => '1.100000'],
                ['assetCode' => 'Conservative', 'unitPrice' => '1.000000'],
            ],
        ]);

        $this->postJson('/mock/moveDayForward', ['days' => 2]);

        // Day 2: 500 total, 50% each → 250 each at price 1.0 → 250 units each
        // Day 3: 300 total, 50% each → 150 each
        //   Balanced: new_units = 250 + 150/1.1 = 250 + 136.363636 = 386.363636
        //   Conservative: new_units = 250 + 150/1.0 = 400.000000
        //   Balanced balance = 386.363636 * 1.1 = 425.00 (rounded)
        //   Conservative balance = 400 * 1.0 = 400.00

        $holdingsResponse = $this->postJson('/public/getHoldings', [
            'userId' => 'user-lifecycle-002',
            'memberId' => $memberId,
            'accountId' => $accountId,
        ]);

        $holdings = collect($holdingsResponse->json('holdings'))->keyBy('assetCode');

        $this->assertEquals('2024-01-03', $holdings['Balanced']['effectiveDate']);
        $this->assertEquals('2024-01-03', $holdings['Conservative']['effectiveDate']);

        // Conservative: 250 + 150/1.0 = 400 units, balance = 400.00
        $this->assertEqualsWithDelta(400.0, $holdings['Conservative']['units'], 0.01);
        $this->assertEqualsWithDelta(400.0, $holdings['Conservative']['balance'], 0.01);

        // Balanced: 250 + 150/1.1 ≈ 386.363636 units, balance ≈ 425.00
        $this->assertEqualsWithDelta(386.36, $holdings['Balanced']['units'], 0.01);
        $this->assertEqualsWithDelta(425.0, $holdings['Balanced']['balance'], 0.01);
    }

    public function test_profile_change_affects_future_days_only(): void
    {
        $createResponse = $this->postJson('/public/createMember', [
            'userId' => 'user-lifecycle-003',
            'firstName' => 'Profile',
            'lastName' => 'Change',
            'email' => 'profile@example.com',
            'mobile' => '0400000003',
            'dateOfBirth' => '1988-11-10',
            'initialInvestmentProfile' => [
                ['assetCode' => 'Growth', 'percentage' => 100],
            ],
        ]);

        $memberId = $createResponse->json('memberId');
        $accountId = $createResponse->json('accountId');

        $this->postJson('/mock/addTransactions', [
            'userId' => 'user-lifecycle-003',
            'accountId' => $accountId,
            'transactions' => [
                ['type' => 'contribution', 'amount' => '1000.00', 'effectiveDate' => '2024-01-02'],
                ['type' => 'contribution', 'amount' => '1000.00', 'effectiveDate' => '2024-01-03'],
            ],
        ]);

        $this->postJson('/mock/setDailyUnitPrices', [
            'date' => '2024-01-02',
            'prices' => [['assetCode' => 'Growth', 'unitPrice' => '1.000000']],
        ]);

        $this->postJson('/mock/setDailyUnitPrices', [
            'date' => '2024-01-03',
            'prices' => [
                ['assetCode' => 'Growth', 'unitPrice' => '1.000000'],
                ['assetCode' => 'Cash', 'unitPrice' => '1.000000'],
            ],
        ]);

        // Process day 2 with 100% Growth
        $this->postJson('/mock/moveDayForward', ['days' => 1]);

        // Day 2 holdings: 1000 units Growth
        $day2Holdings = $this->postJson('/public/getHoldings', [
            'userId' => 'user-lifecycle-003',
            'memberId' => $memberId,
            'accountId' => $accountId,
            'asOfDate' => '2024-01-02',
        ]);

        $day2ByAsset = collect($day2Holdings->json('holdings'))->keyBy('assetCode');
        $this->assertEqualsWithDelta(1000.0, $day2ByAsset['Growth']['units'], 0.01);

        // Change profile to 50/50
        $this->postJson('/public/setInvestmentProfile', [
            'userId' => 'user-lifecycle-003',
            'memberId' => $memberId,
            'accountId' => $accountId,
            'allocations' => [
                ['assetCode' => 'Growth', 'percentage' => 50],
                ['assetCode' => 'Cash', 'percentage' => 50],
            ],
        ]);

        // Process day 3 with new 50/50 profile
        $this->postJson('/mock/moveDayForward', ['days' => 1]);

        // Day 3: 1000 cash flow, 50% each → 500 Growth, 500 Cash
        // Growth: 1000 (prev) + 500/1.0 = 1500 units
        // Cash: 0 (prev) + 500/1.0 = 500 units
        $day3Holdings = $this->postJson('/public/getHoldings', [
            'userId' => 'user-lifecycle-003',
            'memberId' => $memberId,
            'accountId' => $accountId,
            'asOfDate' => '2024-01-03',
        ]);

        $day3ByAsset = collect($day3Holdings->json('holdings'))->keyBy('assetCode');
        $this->assertEqualsWithDelta(1500.0, $day3ByAsset['Growth']['units'], 0.01);
        $this->assertEqualsWithDelta(500.0, $day3ByAsset['Cash']['units'], 0.01);

        // Day 2 snapshot remains unchanged
        $day2Again = $this->postJson('/public/getHoldings', [
            'userId' => 'user-lifecycle-003',
            'memberId' => $memberId,
            'accountId' => $accountId,
            'asOfDate' => '2024-01-02',
        ]);

        $day2AgainByAsset = collect($day2Again->json('holdings'))->keyBy('assetCode');
        $this->assertEqualsWithDelta(1000.0, $day2AgainByAsset['Growth']['units'], 0.01);
        $this->assertArrayNotHasKey('Cash', $day2AgainByAsset->toArray());
    }
}
