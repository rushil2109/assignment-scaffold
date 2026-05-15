<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateMemberTest extends TestCase
{
    use RefreshDatabase;

    private function createMember(): array
    {
        $response = $this->postJson('/public/createMember', [
            'userId' => 'user-update-001',
            'firstName' => 'Original',
            'lastName' => 'Name',
            'email' => 'original@example.com',
            'mobile' => '0400000000',
            'dateOfBirth' => '1990-01-01',
            'initialInvestmentProfile' => [
                ['assetCode' => 'Growth', 'percentage' => 100],
            ],
        ]);

        return $response->json();
    }

    public function test_update_member_email_persists(): void
    {
        $member = $this->createMember();

        $response = $this->postJson('/public/updateMember', [
            'userId' => 'user-update-001',
            'memberId' => $member['memberId'],
            'email' => 'updated@example.com',
        ]);

        $response->assertOk();
        $response->assertJson(['ok' => true]);
        $this->assertNotEmpty($response->json('operationId'));

        $this->assertDatabaseHas('members', [
            'user_id' => 'user-update-001',
            'email' => 'updated@example.com',
        ]);
    }

    public function test_update_member_mobile_persists(): void
    {
        $member = $this->createMember();

        $response = $this->postJson('/public/updateMember', [
            'userId' => 'user-update-001',
            'memberId' => $member['memberId'],
            'mobile' => '0499999999',
        ]);

        $response->assertOk();
        $response->assertJson(['ok' => true]);

        $this->assertDatabaseHas('members', [
            'user_id' => 'user-update-001',
            'mobile' => '0499999999',
        ]);
    }

    public function test_update_member_preferred_name(): void
    {
        $member = $this->createMember();

        $response = $this->postJson('/public/updateMember', [
            'userId' => 'user-update-001',
            'memberId' => $member['memberId'],
            'preferredName' => 'Preferred',
        ]);

        $response->assertOk();
        $response->assertJson(['ok' => true]);

        $this->assertDatabaseHas('members', [
            'user_id' => 'user-update-001',
            'preferred_name' => 'Preferred',
        ]);
    }

    public function test_update_member_not_found(): void
    {
        $response = $this->postJson('/public/updateMember', [
            'userId' => 'user-nonexistent',
            'memberId' => 'fake-id',
            'email' => 'x@example.com',
        ]);

        $response->assertOk();
        $response->assertJson(['ok' => false, 'error' => 'Member not found.']);
    }

    public function test_update_member_no_fields_returns_error(): void
    {
        $member = $this->createMember();

        $response = $this->postJson('/public/updateMember', [
            'userId' => 'user-update-001',
            'memberId' => $member['memberId'],
        ]);

        $response->assertOk();
        $response->assertJson(['ok' => false]);
    }

    public function test_update_member_emits_audit(): void
    {
        $member = $this->createMember();

        $response = $this->postJson('/public/updateMember', [
            'userId' => 'user-update-001',
            'memberId' => $member['memberId'],
            'email' => 'audited@example.com',
        ]);

        $operationId = $response->json('operationId');

        $auditResponse = $this->postJson('/inspection/getRequestAudit', [
            'userId' => 'user-update-001',
            'operationId' => $operationId,
        ]);

        $auditResponse->assertJson([
            'ok' => true,
            'audit' => [
                'operation' => 'updateMember',
                'status' => 'success',
            ],
        ]);

        $events = $auditResponse->json('audit.events');
        $this->assertEquals('member_updated', $events[0]['type']);
    }
}
