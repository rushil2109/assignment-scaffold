<?php

namespace Tests\Feature;

use App\Models\AuditOperation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PublicApiReadEndpointsTest extends TestCase
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

    private function createMember(): array
    {
        $response = $this->postJson('/public/createMember', [
            'userId' => 'user-001',
            'firstName' => 'Test',
            'lastName' => 'User',
            'email' => 'test@example.com',
            'mobile' => '0400000000',
            'dateOfBirth' => '1990-01-01',
            'initialInvestmentProfile' => [
                ['assetCode' => 'Growth', 'percentage' => 60],
                ['assetCode' => 'Cash', 'percentage' => 40],
            ],
        ]);

        return $response->json();
    }

    private function setupHoldings(string $accountId): void
    {
        $this->postJson('/mock/addTransactions', [
            'userId' => 'user-001',
            'accountId' => $accountId,
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
                ['assetCode' => 'Growth', 'unitPrice' => '1.010000'],
                ['assetCode' => 'Cash', 'unitPrice' => '1.000000'],
            ],
        ]);

        $this->postJson('/mock/moveDayForward', ['days' => 2]);
    }

    public function test_get_investment_portfolio_returns_current_allocations(): void
    {
        $member = $this->createMember();

        $response = $this->postJson('/public/getInvestmentPortfolio', [
            'userId' => 'user-001',
            'memberId' => $member['memberId'],
            'accountId' => $member['accountId'],
        ]);

        $response->assertOk();
        $response->assertJson([
            'ok' => true,
            'allocations' => [
                ['assetCode' => 'Growth', 'percentage' => 60],
                ['assetCode' => 'Cash', 'percentage' => 40],
            ],
        ]);
    }

    public function test_get_transaction_history_without_date_filters(): void
    {
        $member = $this->createMember();

        $this->postJson('/mock/addTransactions', [
            'userId' => 'user-001',
            'accountId' => $member['accountId'],
            'transactions' => [
                ['type' => 'contribution', 'amount' => '1000.00', 'effectiveDate' => '2024-01-01'],
                ['type' => 'contribution', 'amount' => '500.00', 'effectiveDate' => '2024-01-02'],
            ],
        ]);

        $response = $this->postJson('/public/getTransactionHistory', [
            'userId' => 'user-001',
            'memberId' => $member['memberId'],
            'accountId' => $member['accountId'],
        ]);

        $response->assertOk();
        $response->assertJson(['ok' => true]);
        $this->assertCount(2, $response->json('transactions'));
        $this->assertEquals('2024-01-01', $response->json('transactions.0.effectiveDate'));
        $this->assertEquals('2024-01-02', $response->json('transactions.1.effectiveDate'));
    }

    public function test_get_transaction_history_with_date_filters(): void
    {
        $member = $this->createMember();

        $this->postJson('/mock/addTransactions', [
            'userId' => 'user-001',
            'accountId' => $member['accountId'],
            'transactions' => [
                ['type' => 'contribution', 'amount' => '1000.00', 'effectiveDate' => '2024-01-01'],
                ['type' => 'contribution', 'amount' => '500.00', 'effectiveDate' => '2024-01-02'],
                ['type' => 'contribution', 'amount' => '200.00', 'effectiveDate' => '2024-01-03'],
            ],
        ]);

        $response = $this->postJson('/public/getTransactionHistory', [
            'userId' => 'user-001',
            'memberId' => $member['memberId'],
            'accountId' => $member['accountId'],
            'fromDate' => '2024-01-02',
            'toDate' => '2024-01-02',
        ]);

        $response->assertOk();
        $response->assertJson(['ok' => true]);
        $this->assertCount(1, $response->json('transactions'));
        $this->assertEquals(500, $response->json('transactions.0.amount'));
    }

    public function test_get_holdings_with_as_of_date(): void
    {
        $member = $this->createMember();
        $this->setupHoldings($member['accountId']);

        $response = $this->postJson('/public/getHoldings', [
            'userId' => 'user-001',
            'memberId' => $member['memberId'],
            'accountId' => $member['accountId'],
            'asOfDate' => '2024-01-02',
        ]);

        $response->assertOk();
        $response->assertJson(['ok' => true]);
        $this->assertNotEmpty($response->json('holdings'));
        $this->assertEquals('2024-01-02', $response->json('holdings.0.effectiveDate'));
    }

    public function test_get_holdings_without_as_of_date_returns_latest(): void
    {
        $member = $this->createMember();
        $this->setupHoldings($member['accountId']);

        $response = $this->postJson('/public/getHoldings', [
            'userId' => 'user-001',
            'memberId' => $member['memberId'],
            'accountId' => $member['accountId'],
        ]);

        $response->assertOk();
        $response->assertJson(['ok' => true]);
        $this->assertNotEmpty($response->json('holdings'));
        $this->assertEquals('2024-01-03', $response->json('holdings.0.effectiveDate'));
    }

    public function test_get_holdings_empty_result(): void
    {
        $member = $this->createMember();

        $response = $this->postJson('/public/getHoldings', [
            'userId' => 'user-001',
            'memberId' => $member['memberId'],
            'accountId' => $member['accountId'],
        ]);

        $response->assertOk();
        $response->assertJson(['ok' => true, 'holdings' => []]);
    }

    public function test_no_audit_rows_for_read_operations(): void
    {
        $member = $this->createMember();
        $auditCountBefore = AuditOperation::count();

        $this->postJson('/public/getInvestmentPortfolio', [
            'userId' => 'user-001',
            'memberId' => $member['memberId'],
            'accountId' => $member['accountId'],
        ]);

        $this->postJson('/public/getTransactionHistory', [
            'userId' => 'user-001',
            'memberId' => $member['memberId'],
            'accountId' => $member['accountId'],
        ]);

        $this->postJson('/public/getHoldings', [
            'userId' => 'user-001',
            'memberId' => $member['memberId'],
            'accountId' => $member['accountId'],
        ]);

        $this->assertEquals($auditCountBefore, AuditOperation::count());
    }

    public function test_missing_member_id_returns_error(): void
    {
        $response = $this->postJson('/public/getInvestmentPortfolio', [
            'userId' => 'user-001',
            'accountId' => 'some-account',
        ]);

        $response->assertOk();
        $response->assertJson(['ok' => false]);
    }

    public function test_missing_account_id_returns_error(): void
    {
        $response = $this->postJson('/public/getTransactionHistory', [
            'userId' => 'user-001',
            'memberId' => 'some-member',
        ]);

        $response->assertOk();
        $response->assertJson(['ok' => false]);
    }

    public function test_member_not_found_returns_error(): void
    {
        $response = $this->postJson('/public/getHoldings', [
            'userId' => 'nonexistent',
            'memberId' => 'fake-member',
            'accountId' => 'fake-account',
        ]);

        $response->assertOk();
        $response->assertJson(['ok' => false, 'error' => 'Member not found.']);
    }
}
