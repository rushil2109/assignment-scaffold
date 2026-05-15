<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SetInvestmentProfileTest extends TestCase
{
    use RefreshDatabase;

    private function createMember(): array
    {
        $response = $this->postJson('/public/createMember', [
            'userId' => 'user-profile-001',
            'firstName' => 'Profile',
            'lastName' => 'Test',
            'email' => 'profile@example.com',
            'mobile' => '0400000000',
            'dateOfBirth' => '1990-01-01',
            'initialInvestmentProfile' => [
                ['assetCode' => 'Growth', 'percentage' => 100],
            ],
        ]);

        return $response->json();
    }

    public function test_set_profile_reflected_in_get_portfolio(): void
    {
        $member = $this->createMember();

        $setResponse = $this->postJson('/public/setInvestmentProfile', [
            'userId' => 'user-profile-001',
            'memberId' => $member['memberId'],
            'accountId' => $member['accountId'],
            'allocations' => [
                ['assetCode' => 'Balanced', 'percentage' => 70],
                ['assetCode' => 'Cash', 'percentage' => 30],
            ],
        ]);

        $setResponse->assertOk();
        $setResponse->assertJson(['ok' => true]);
        $this->assertNotEmpty($setResponse->json('operationId'));

        $getResponse = $this->postJson('/public/getInvestmentPortfolio', [
            'userId' => 'user-profile-001',
            'memberId' => $member['memberId'],
            'accountId' => $member['accountId'],
        ]);

        $getResponse->assertOk();
        $getResponse->assertJson([
            'ok' => true,
            'allocations' => [
                ['assetCode' => 'Balanced', 'percentage' => 70],
                ['assetCode' => 'Cash', 'percentage' => 30],
            ],
        ]);
    }

    public function test_set_profile_invalid_total_not_100(): void
    {
        $member = $this->createMember();

        $response = $this->postJson('/public/setInvestmentProfile', [
            'userId' => 'user-profile-001',
            'memberId' => $member['memberId'],
            'accountId' => $member['accountId'],
            'allocations' => [
                ['assetCode' => 'Growth', 'percentage' => 80],
            ],
        ]);

        $response->assertOk();
        $response->assertJson(['ok' => false]);
    }

    public function test_set_profile_invalid_asset_code(): void
    {
        $member = $this->createMember();

        $response = $this->postJson('/public/setInvestmentProfile', [
            'userId' => 'user-profile-001',
            'memberId' => $member['memberId'],
            'accountId' => $member['accountId'],
            'allocations' => [
                ['assetCode' => 'InvalidAsset', 'percentage' => 100],
            ],
        ]);

        $response->assertOk();
        $response->assertJson(['ok' => false]);
    }

    public function test_set_profile_emits_audit(): void
    {
        $member = $this->createMember();

        $response = $this->postJson('/public/setInvestmentProfile', [
            'userId' => 'user-profile-001',
            'memberId' => $member['memberId'],
            'accountId' => $member['accountId'],
            'allocations' => [
                ['assetCode' => 'HighGrowth', 'percentage' => 100],
            ],
        ]);

        $operationId = $response->json('operationId');

        $auditResponse = $this->postJson('/inspection/getRequestAudit', [
            'userId' => 'user-profile-001',
            'operationId' => $operationId,
        ]);

        $auditResponse->assertJson([
            'ok' => true,
            'audit' => [
                'operation' => 'setInvestmentProfile',
                'status' => 'success',
            ],
        ]);

        $events = $auditResponse->json('audit.events');
        $this->assertEquals('profile_updated', $events[0]['type']);
    }

    public function test_initial_profile_visible_via_get_portfolio(): void
    {
        $member = $this->createMember();

        $response = $this->postJson('/public/getInvestmentPortfolio', [
            'userId' => 'user-profile-001',
            'memberId' => $member['memberId'],
            'accountId' => $member['accountId'],
        ]);

        $response->assertOk();
        $response->assertJson([
            'ok' => true,
            'allocations' => [
                ['assetCode' => 'Growth', 'percentage' => 100],
            ],
        ]);
    }
}
