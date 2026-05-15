<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicApiUpdateAndProfileTest extends TestCase
{
    use RefreshDatabase;

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

    public function test_update_member_with_valid_partial_data(): void
    {
        $member = $this->createMember();

        $response = $this->postJson('/public/updateMember', [
            'userId' => 'user-001',
            'memberId' => $member['memberId'],
            'email' => 'new@example.com',
            'mobile' => '0411111111',
        ]);

        $response->assertOk();
        $response->assertJson(['ok' => true]);
        $this->assertArrayHasKey('operationId', $response->json());

        $this->assertDatabaseHas('members', [
            'user_id' => 'user-001',
            'email' => 'new@example.com',
            'mobile' => '0411111111',
        ]);

        $this->assertDatabaseHas('audit_operations', [
            'user_id' => 'user-001',
            'operation' => 'updateMember',
            'status' => 'success',
        ]);

        $this->assertDatabaseHas('audit_events', [
            'type' => 'member_updated',
        ]);
    }

    public function test_update_member_missing_member_id_returns_error(): void
    {
        $response = $this->postJson('/public/updateMember', [
            'userId' => 'user-001',
            'email' => 'new@example.com',
        ]);

        $response->assertOk();
        $response->assertJson(['ok' => false]);
    }

    public function test_update_member_not_found_returns_error(): void
    {
        $response = $this->postJson('/public/updateMember', [
            'userId' => 'user-nonexistent',
            'memberId' => 'fake-member-id',
            'email' => 'new@example.com',
        ]);

        $response->assertOk();
        $response->assertJson(['ok' => false, 'error' => 'Member not found.']);
    }

    public function test_update_member_no_updatable_fields_returns_error(): void
    {
        $member = $this->createMember();

        $response = $this->postJson('/public/updateMember', [
            'userId' => 'user-001',
            'memberId' => $member['memberId'],
        ]);

        $response->assertOk();
        $response->assertJson(['ok' => false]);
    }

    public function test_set_investment_profile_with_valid_data(): void
    {
        $member = $this->createMember();

        $response = $this->postJson('/public/setInvestmentProfile', [
            'userId' => 'user-001',
            'memberId' => $member['memberId'],
            'accountId' => $member['accountId'],
            'allocations' => [
                ['assetCode' => 'Conservative', 'percentage' => 50],
                ['assetCode' => 'Balanced', 'percentage' => 50],
            ],
        ]);

        $response->assertOk();
        $response->assertJson(['ok' => true]);
        $this->assertArrayHasKey('operationId', $response->json());

        $this->assertDatabaseHas('investment_profiles', [
            'asset_code' => 'Conservative',
            'percentage' => '50.00',
            'is_current' => true,
        ]);

        $this->assertDatabaseHas('audit_operations', [
            'user_id' => 'user-001',
            'operation' => 'setInvestmentProfile',
            'status' => 'success',
        ]);

        $this->assertDatabaseHas('audit_events', [
            'type' => 'profile_updated',
        ]);
    }

    public function test_set_investment_profile_missing_account_id_returns_error(): void
    {
        $member = $this->createMember();

        $response = $this->postJson('/public/setInvestmentProfile', [
            'userId' => 'user-001',
            'memberId' => $member['memberId'],
            'allocations' => [
                ['assetCode' => 'Growth', 'percentage' => 100],
            ],
        ]);

        $response->assertOk();
        $response->assertJson(['ok' => false]);
    }

    public function test_set_investment_profile_invalid_allocations_returns_error(): void
    {
        $member = $this->createMember();

        $response = $this->postJson('/public/setInvestmentProfile', [
            'userId' => 'user-001',
            'memberId' => $member['memberId'],
            'accountId' => $member['accountId'],
            'allocations' => [
                ['assetCode' => 'Growth', 'percentage' => 50],
                ['assetCode' => 'Cash', 'percentage' => 30],
            ],
        ]);

        $response->assertOk();
        $response->assertJson(['ok' => false]);
    }
}
