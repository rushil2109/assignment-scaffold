<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\SeedsSystemState;

class ContractTest extends TestCase
{
    use RefreshDatabase;
    use SeedsSystemState;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedSystemState('2024-01-01');
    }

    private function createMember(): array
    {
        $response = $this->postJson('/public/createMember', [
            'userId' => 'user-contract-001',
            'firstName' => 'Contract',
            'lastName' => 'Test',
            'email' => 'contract@example.com',
            'mobile' => '0400000000',
            'dateOfBirth' => '1990-01-01',
            'initialInvestmentProfile' => [
                ['assetCode' => 'Growth', 'percentage' => 60],
                ['assetCode' => 'Cash', 'percentage' => 40],
            ],
        ]);

        return $response->json();
    }

    public function test_create_member_response_uses_camel_case(): void
    {
        $response = $this->postJson('/public/createMember', [
            'userId' => 'user-contract-cc',
            'firstName' => 'CamelCase',
            'lastName' => 'Test',
            'email' => 'camel@example.com',
            'mobile' => '0400000000',
            'dateOfBirth' => '1990-01-01',
            'initialInvestmentProfile' => [
                ['assetCode' => 'Growth', 'percentage' => 100],
            ],
        ]);

        $data = $response->json();
        $this->assertArrayHasKey('ok', $data);
        $this->assertArrayHasKey('memberId', $data);
        $this->assertArrayHasKey('accountId', $data);
        $this->assertArrayHasKey('operationId', $data);

        // Ensure no snake_case variants
        $this->assertArrayNotHasKey('member_id', $data);
        $this->assertArrayNotHasKey('account_id', $data);
        $this->assertArrayNotHasKey('operation_id', $data);
    }

    public function test_get_investment_portfolio_response_uses_camel_case(): void
    {
        $member = $this->createMember();

        $response = $this->postJson('/public/getInvestmentPortfolio', [
            'userId' => 'user-contract-001',
            'memberId' => $member['memberId'],
            'accountId' => $member['accountId'],
        ]);

        $data = $response->json();
        $this->assertArrayHasKey('ok', $data);
        $this->assertArrayHasKey('allocations', $data);

        $allocation = $data['allocations'][0];
        $this->assertArrayHasKey('assetCode', $allocation);
        $this->assertArrayHasKey('percentage', $allocation);
        $this->assertArrayNotHasKey('asset_code', $allocation);
    }

    public function test_get_transaction_history_response_uses_camel_case(): void
    {
        $member = $this->createMember();

        $this->postJson('/mock/addTransactions', [
            'userId' => 'user-contract-001',
            'accountId' => $member['accountId'],
            'transactions' => [
                ['type' => 'contribution', 'amount' => '500.00', 'effectiveDate' => '2024-01-02'],
            ],
        ]);

        $response = $this->postJson('/public/getTransactionHistory', [
            'userId' => 'user-contract-001',
            'memberId' => $member['memberId'],
            'accountId' => $member['accountId'],
        ]);

        $data = $response->json();
        $this->assertArrayHasKey('ok', $data);
        $this->assertArrayHasKey('transactions', $data);

        $txn = $data['transactions'][0];
        $this->assertArrayHasKey('transactionId', $txn);
        $this->assertArrayHasKey('effectiveDate', $txn);
        $this->assertArrayNotHasKey('transaction_id', $txn);
        $this->assertArrayNotHasKey('effective_date', $txn);
    }

    public function test_get_holdings_response_uses_camel_case(): void
    {
        $member = $this->createMember();

        $this->postJson('/mock/addTransactions', [
            'userId' => 'user-contract-001',
            'accountId' => $member['accountId'],
            'transactions' => [
                ['type' => 'contribution', 'amount' => '1000.00', 'effectiveDate' => '2024-01-02'],
            ],
        ]);

        $this->postJson('/mock/setDailyUnitPrices', [
            'date' => '2024-01-02',
            'prices' => [
                ['assetCode' => 'Growth', 'unitPrice' => '2.000000'],
                ['assetCode' => 'Cash', 'unitPrice' => '1.000000'],
            ],
        ]);

        $this->postJson('/mock/moveDayForward', ['days' => 1]);

        $response = $this->postJson('/public/getHoldings', [
            'userId' => 'user-contract-001',
            'memberId' => $member['memberId'],
            'accountId' => $member['accountId'],
        ]);

        $data = $response->json();
        $this->assertArrayHasKey('ok', $data);
        $this->assertArrayHasKey('holdings', $data);

        $holding = $data['holdings'][0];
        $this->assertArrayHasKey('assetCode', $holding);
        $this->assertArrayHasKey('units', $holding);
        $this->assertArrayHasKey('unitPrice', $holding);
        $this->assertArrayHasKey('balance', $holding);
        $this->assertArrayHasKey('effectiveDate', $holding);
        $this->assertArrayNotHasKey('asset_code', $holding);
        $this->assertArrayNotHasKey('unit_price', $holding);
        $this->assertArrayNotHasKey('effective_date', $holding);
    }

    public function test_inspection_api_response_uses_camel_case(): void
    {
        $member = $this->createMember();

        $response = $this->postJson('/inspection/getRequestAudit', [
            'userId' => 'user-contract-001',
            'operationId' => $member['operationId'],
        ]);

        $data = $response->json();
        $this->assertArrayHasKey('ok', $data);
        $this->assertArrayHasKey('audit', $data);

        $audit = $data['audit'];
        $this->assertArrayHasKey('userId', $audit);
        $this->assertArrayHasKey('operationId', $audit);
        $this->assertArrayNotHasKey('user_id', $audit);
        $this->assertArrayNotHasKey('operation_id', $audit);
    }

    public function test_all_success_responses_have_ok_true(): void
    {
        $member = $this->createMember();
        $this->assertTrue($member['ok']);

        $portfolio = $this->postJson('/public/getInvestmentPortfolio', [
            'userId' => 'user-contract-001',
            'memberId' => $member['memberId'],
            'accountId' => $member['accountId'],
        ]);
        $this->assertTrue($portfolio->json('ok'));

        $txnHistory = $this->postJson('/public/getTransactionHistory', [
            'userId' => 'user-contract-001',
            'memberId' => $member['memberId'],
            'accountId' => $member['accountId'],
        ]);
        $this->assertTrue($txnHistory->json('ok'));

        $holdings = $this->postJson('/public/getHoldings', [
            'userId' => 'user-contract-001',
            'memberId' => $member['memberId'],
            'accountId' => $member['accountId'],
        ]);
        $this->assertTrue($holdings->json('ok'));

        $audit = $this->postJson('/inspection/getRequestAudit', [
            'userId' => 'user-contract-001',
            'operationId' => $member['operationId'],
        ]);
        $this->assertTrue($audit->json('ok'));

        $events = $this->postJson('/inspection/listAuditEvents', [
            'userId' => 'user-contract-001',
        ]);
        $this->assertTrue($events->json('ok'));
    }

    public function test_all_error_responses_have_ok_false(): void
    {
        $response = $this->postJson('/public/getHoldings', [
            'userId' => 'nonexistent',
            'memberId' => 'fake',
            'accountId' => 'fake',
        ]);
        $this->assertFalse($response->json('ok'));

        $response = $this->postJson('/inspection/getRequestAudit', [
            'userId' => 'user-contract-001',
            'operationId' => 'nonexistent',
        ]);
        $this->assertFalse($response->json('ok'));
    }
}
