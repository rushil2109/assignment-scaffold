<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\SeedsSystemState;

class GetHoldingsTest extends TestCase
{
    use RefreshDatabase;
    use SeedsSystemState;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedSystemState('2024-01-01');
    }

    private function createMemberWithHoldings(): array
    {
        $response = $this->postJson('/public/createMember', [
            'userId' => 'user-holdings-001',
            'firstName' => 'Holdings',
            'lastName' => 'Test',
            'email' => 'holdings@example.com',
            'mobile' => '0400000000',
            'dateOfBirth' => '1990-01-01',
            'initialInvestmentProfile' => [
                ['assetCode' => 'Growth', 'percentage' => 60],
                ['assetCode' => 'Cash', 'percentage' => 40],
            ],
        ]);

        $member = $response->json();

        $this->postJson('/mock/addTransactions', [
            'userId' => 'user-holdings-001',
            'accountId' => $member['accountId'],
            'transactions' => [
                ['type' => 'contribution', 'amount' => '1000.00', 'effectiveDate' => '2024-01-02'],
                ['type' => 'contribution', 'amount' => '500.00', 'effectiveDate' => '2024-01-03'],
            ],
        ]);

        $this->postJson('/mock/setDailyUnitPrices', [
            'date' => '2024-01-02',
            'prices' => [
                ['assetCode' => 'Growth', 'unitPrice' => '1.000000'],
                ['assetCode' => 'Cash', 'unitPrice' => '1.000000'],
            ],
        ]);

        $this->postJson('/mock/setDailyUnitPrices', [
            'date' => '2024-01-03',
            'prices' => [
                ['assetCode' => 'Growth', 'unitPrice' => '1.500000'],
                ['assetCode' => 'Cash', 'unitPrice' => '1.000000'],
            ],
        ]);

        $this->postJson('/mock/moveDayForward', ['days' => 2]);

        return $member;
    }

    public function test_get_holdings_without_as_of_date_returns_latest(): void
    {
        $member = $this->createMemberWithHoldings();

        $response = $this->postJson('/public/getHoldings', [
            'userId' => 'user-holdings-001',
            'memberId' => $member['memberId'],
            'accountId' => $member['accountId'],
        ]);

        $response->assertOk();
        $response->assertJson(['ok' => true]);

        $holdings = $response->json('holdings');
        $this->assertNotEmpty($holdings);

        foreach ($holdings as $holding) {
            $this->assertEquals('2024-01-03', $holding['effectiveDate']);
        }
    }

    public function test_get_holdings_with_as_of_date_returns_historical(): void
    {
        $member = $this->createMemberWithHoldings();

        $response = $this->postJson('/public/getHoldings', [
            'userId' => 'user-holdings-001',
            'memberId' => $member['memberId'],
            'accountId' => $member['accountId'],
            'asOfDate' => '2024-01-02',
        ]);

        $response->assertOk();
        $holdings = $response->json('holdings');
        $this->assertNotEmpty($holdings);

        foreach ($holdings as $holding) {
            $this->assertEquals('2024-01-02', $holding['effectiveDate']);
        }
    }

    public function test_get_holdings_response_contains_all_required_keys(): void
    {
        $member = $this->createMemberWithHoldings();

        $response = $this->postJson('/public/getHoldings', [
            'userId' => 'user-holdings-001',
            'memberId' => $member['memberId'],
            'accountId' => $member['accountId'],
        ]);

        $holding = $response->json('holdings.0');
        $this->assertArrayHasKey('assetCode', $holding);
        $this->assertArrayHasKey('units', $holding);
        $this->assertArrayHasKey('unitPrice', $holding);
        $this->assertArrayHasKey('balance', $holding);
        $this->assertArrayHasKey('effectiveDate', $holding);
    }

    public function test_get_holdings_empty_when_no_processing(): void
    {
        $response = $this->postJson('/public/createMember', [
            'userId' => 'user-holdings-002',
            'firstName' => 'Empty',
            'lastName' => 'Holdings',
            'email' => 'empty@example.com',
            'mobile' => '0400000000',
            'dateOfBirth' => '1990-01-01',
            'initialInvestmentProfile' => [
                ['assetCode' => 'Growth', 'percentage' => 100],
            ],
        ]);

        $member = $response->json();

        $holdingsResponse = $this->postJson('/public/getHoldings', [
            'userId' => 'user-holdings-002',
            'memberId' => $member['memberId'],
            'accountId' => $member['accountId'],
        ]);

        $holdingsResponse->assertOk();
        $holdingsResponse->assertJson(['ok' => true, 'holdings' => []]);
    }

    public function test_balance_equals_units_times_unit_price(): void
    {
        $member = $this->createMemberWithHoldings();

        $response = $this->postJson('/public/getHoldings', [
            'userId' => 'user-holdings-001',
            'memberId' => $member['memberId'],
            'accountId' => $member['accountId'],
        ]);

        $holdings = $response->json('holdings');

        foreach ($holdings as $holding) {
            $expected = round($holding['units'] * $holding['unitPrice'], 2);
            $this->assertEqualsWithDelta($expected, $holding['balance'], 0.01);
        }
    }
}
