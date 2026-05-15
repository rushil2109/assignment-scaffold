<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GetTransactionHistoryTest extends TestCase
{
    use RefreshDatabase;

    private function createMemberWithTransactions(): array
    {
        $response = $this->postJson('/public/createMember', [
            'userId' => 'user-txn-001',
            'firstName' => 'Txn',
            'lastName' => 'Test',
            'email' => 'txn@example.com',
            'mobile' => '0400000000',
            'dateOfBirth' => '1990-01-01',
            'initialInvestmentProfile' => [
                ['assetCode' => 'Growth', 'percentage' => 100],
            ],
        ]);

        $member = $response->json();

        $this->postJson('/mock/addTransactions', [
            'userId' => 'user-txn-001',
            'accountId' => $member['accountId'],
            'transactions' => [
                ['type' => 'contribution', 'amount' => '1000.00', 'effectiveDate' => '2024-01-01'],
                ['type' => 'contribution', 'amount' => '500.00', 'effectiveDate' => '2024-01-05'],
                ['type' => 'withdrawal', 'amount' => '-200.00', 'effectiveDate' => '2024-01-10'],
                ['type' => 'contribution', 'amount' => '300.00', 'effectiveDate' => '2024-01-15'],
            ],
        ]);

        return $member;
    }

    public function test_get_all_transactions_without_filter(): void
    {
        $member = $this->createMemberWithTransactions();

        $response = $this->postJson('/public/getTransactionHistory', [
            'userId' => 'user-txn-001',
            'memberId' => $member['memberId'],
            'accountId' => $member['accountId'],
        ]);

        $response->assertOk();
        $response->assertJson(['ok' => true]);
        $this->assertCount(4, $response->json('transactions'));
    }

    public function test_filter_by_from_date(): void
    {
        $member = $this->createMemberWithTransactions();

        $response = $this->postJson('/public/getTransactionHistory', [
            'userId' => 'user-txn-001',
            'memberId' => $member['memberId'],
            'accountId' => $member['accountId'],
            'fromDate' => '2024-01-05',
        ]);

        $response->assertOk();
        $transactions = $response->json('transactions');
        $this->assertCount(3, $transactions);

        foreach ($transactions as $txn) {
            $this->assertGreaterThanOrEqual('2024-01-05', $txn['effectiveDate']);
        }
    }

    public function test_filter_by_to_date(): void
    {
        $member = $this->createMemberWithTransactions();

        $response = $this->postJson('/public/getTransactionHistory', [
            'userId' => 'user-txn-001',
            'memberId' => $member['memberId'],
            'accountId' => $member['accountId'],
            'toDate' => '2024-01-05',
        ]);

        $response->assertOk();
        $transactions = $response->json('transactions');
        $this->assertCount(2, $transactions);

        foreach ($transactions as $txn) {
            $this->assertLessThanOrEqual('2024-01-05', $txn['effectiveDate']);
        }
    }

    public function test_filter_by_date_range(): void
    {
        $member = $this->createMemberWithTransactions();

        $response = $this->postJson('/public/getTransactionHistory', [
            'userId' => 'user-txn-001',
            'memberId' => $member['memberId'],
            'accountId' => $member['accountId'],
            'fromDate' => '2024-01-05',
            'toDate' => '2024-01-10',
        ]);

        $response->assertOk();
        $transactions = $response->json('transactions');
        $this->assertCount(2, $transactions);
    }

    public function test_transactions_ordered_by_effective_date(): void
    {
        $member = $this->createMemberWithTransactions();

        $response = $this->postJson('/public/getTransactionHistory', [
            'userId' => 'user-txn-001',
            'memberId' => $member['memberId'],
            'accountId' => $member['accountId'],
        ]);

        $transactions = $response->json('transactions');
        $dates = array_column($transactions, 'effectiveDate');
        $sorted = $dates;
        sort($sorted);
        $this->assertEquals($sorted, $dates);
    }

    public function test_transaction_response_contains_expected_keys(): void
    {
        $member = $this->createMemberWithTransactions();

        $response = $this->postJson('/public/getTransactionHistory', [
            'userId' => 'user-txn-001',
            'memberId' => $member['memberId'],
            'accountId' => $member['accountId'],
        ]);

        $txn = $response->json('transactions.0');
        $this->assertArrayHasKey('transactionId', $txn);
        $this->assertArrayHasKey('effectiveDate', $txn);
        $this->assertArrayHasKey('type', $txn);
        $this->assertArrayHasKey('amount', $txn);
    }
}
