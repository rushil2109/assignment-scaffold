<?php

namespace Tests\Feature;

use App\Contracts\AdminApiInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MockControlEndpointsTest extends TestCase
{
    use RefreshDatabase;

    private function createTestMember(): array
    {
        $api = app(AdminApiInterface::class);

        return $api->createMember('admin-test-001', [
            'userId' => 'user-test-001',
            'email' => 'test@example.com',
        ]);
    }

    public function test_add_transactions_persists_and_visible_via_history(): void
    {
        $member = $this->createTestMember();

        $response = $this->postJson('/mock/addTransactions', [
            'userId' => 'user-test-001',
            'accountId' => $member['accountId'],
            'transactions' => [
                ['type' => 'contribution', 'amount' => '1000.00', 'effectiveDate' => '2024-01-15'],
                ['type' => 'contribution', 'amount' => '500.00', 'effectiveDate' => '2024-01-16'],
                ['type' => 'withdrawal', 'amount' => '-200.00', 'effectiveDate' => '2024-01-17'],
            ],
        ]);

        $response->assertOk();
        $response->assertJson(['ok' => true, 'addedCount' => 3]);

        $api = app(AdminApiInterface::class);
        $history = $api->getTransactionHistory('admin-test-001');

        $this->assertCount(3, $history['transactions']);
        $this->assertEquals('contribution', $history['transactions'][0]['type']);
        $this->assertEquals('1000.00', $history['transactions'][0]['amount']);

        $ids = array_column($history['transactions'], 'transactionId');
        $this->assertCount(3, array_unique($ids));
    }

    public function test_set_daily_unit_prices_upserts(): void
    {
        $response = $this->postJson('/mock/setDailyUnitPrices', [
            'date' => '2024-01-15',
            'prices' => [
                ['assetCode' => 'Cash', 'unitPrice' => '1.000000'],
                ['assetCode' => 'Growth', 'unitPrice' => '2.500000'],
            ],
        ]);

        $response->assertOk();
        $response->assertJson(['ok' => true]);

        $this->assertDatabaseHas('unit_prices', [
            'asset_code' => 'Cash',
            'date' => '2024-01-15',
            'price' => '1.000000',
        ]);

        $this->postJson('/mock/setDailyUnitPrices', [
            'date' => '2024-01-15',
            'prices' => [
                ['assetCode' => 'Growth', 'unitPrice' => '3.000000'],
            ],
        ]);

        $this->assertDatabaseHas('unit_prices', [
            'asset_code' => 'Growth',
            'date' => '2024-01-15',
            'price' => '3.000000',
        ]);

        $this->assertDatabaseCount('unit_prices', 2);
    }

    public function test_mock_operations_create_no_audit_rows(): void
    {
        $member = $this->createTestMember();

        $this->postJson('/mock/addTransactions', [
            'userId' => 'user-test-001',
            'accountId' => $member['accountId'],
            'transactions' => [
                ['type' => 'contribution', 'amount' => '1000.00', 'effectiveDate' => '2024-01-15'],
            ],
        ]);

        $this->postJson('/mock/setDailyUnitPrices', [
            'date' => '2024-01-15',
            'prices' => [
                ['assetCode' => 'Cash', 'unitPrice' => '1.000000'],
            ],
        ]);

        $this->assertDatabaseCount('audit_operations', 0);
        $this->assertDatabaseCount('audit_events', 0);
    }
}
