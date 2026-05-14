<?php

namespace Tests\Feature;

use App\Contracts\AdminApiInterface;
use App\Services\MockAdminApi;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MockAdminApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_interface_resolves_from_container(): void
    {
        $resolved = app(AdminApiInterface::class);

        $this->assertInstanceOf(MockAdminApi::class, $resolved);
    }

    public function test_create_member_persists_data(): void
    {
        $api = app(AdminApiInterface::class);

        $result = $api->createMember('admin-001', [
            'userId' => 'user-123',
            'email' => 'test@example.com',
            'preferredName' => 'Tester',
        ]);

        $this->assertEquals('admin-001', $result['adminId']);
        $this->assertNotEmpty($result['memberId']);
        $this->assertNotEmpty($result['accountId']);

        $this->assertDatabaseHas('members', [
            'admin_id' => 'admin-001',
            'user_id' => 'user-123',
            'email' => 'test@example.com',
            'preferred_name' => 'Tester',
        ]);

        $this->assertDatabaseHas('accounts', [
            'member_id' => $result['memberId'],
        ]);
    }

    public function test_set_investment_profile_append_only(): void
    {
        $api = app(AdminApiInterface::class);

        $api->createMember('admin-002', ['userId' => 'user-456']);

        $first = $api->setInvestmentProfile('admin-002', [
            'effectiveFrom' => '2024-01-01',
            'allocations' => [
                ['assetCode' => 'Growth', 'percentage' => '60.00'],
                ['assetCode' => 'Cash', 'percentage' => '40.00'],
            ],
        ]);

        $this->assertCount(2, $first['allocations']);

        $second = $api->setInvestmentProfile('admin-002', [
            'effectiveFrom' => '2024-02-01',
            'allocations' => [
                ['assetCode' => 'Balanced', 'percentage' => '100.00'],
            ],
        ]);

        $this->assertCount(1, $second['allocations']);

        $portfolio = $api->getInvestmentPortfolio('admin-002');
        $this->assertCount(1, $portfolio['allocations']);
        $this->assertEquals('Balanced', $portfolio['allocations'][0]['assetCode']);

        $this->assertDatabaseCount('investment_profiles', 3);
    }
}
