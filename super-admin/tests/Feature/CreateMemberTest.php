<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateMemberTest extends TestCase
{
    use RefreshDatabase;

    private function validPayload(string $userId = 'user-create-001'): array
    {
        return [
            'userId' => $userId,
            'firstName' => 'Test',
            'lastName' => 'User',
            'email' => 'create@example.com',
            'mobile' => '0400000000',
            'dateOfBirth' => '1990-01-01',
            'initialInvestmentProfile' => [
                ['assetCode' => 'Growth', 'percentage' => 60],
                ['assetCode' => 'Cash', 'percentage' => 40],
            ],
        ];
    }

    public function test_create_member_success(): void
    {
        $response = $this->postJson('/public/createMember', $this->validPayload());

        $response->assertOk();
        $response->assertJson(['ok' => true]);
        $this->assertNotEmpty($response->json('memberId'));
        $this->assertNotEmpty($response->json('accountId'));
        $this->assertNotEmpty($response->json('operationId'));
    }

    public function test_create_member_idempotent_same_user_id(): void
    {
        $first = $this->postJson('/public/createMember', $this->validPayload());
        $second = $this->postJson('/public/createMember', $this->validPayload());

        $first->assertOk();
        $second->assertOk();

        $this->assertTrue($first->json('ok'));
        $this->assertTrue($second->json('ok'));
        $this->assertEquals($first->json('memberId'), $second->json('memberId'));
        $this->assertEquals($first->json('accountId'), $second->json('accountId'));
    }

    public function test_create_member_missing_required_fields(): void
    {
        $response = $this->postJson('/public/createMember', [
            'userId' => 'user-incomplete',
        ]);

        $response->assertOk();
        $response->assertJson(['ok' => false]);
    }

    public function test_create_member_invalid_allocations_not_100(): void
    {
        $payload = $this->validPayload();
        $payload['initialInvestmentProfile'] = [
            ['assetCode' => 'Growth', 'percentage' => 50],
        ];

        $response = $this->postJson('/public/createMember', $payload);

        $response->assertOk();
        $response->assertJson(['ok' => false]);
    }

    public function test_create_member_emits_audit_trail(): void
    {
        $response = $this->postJson('/public/createMember', $this->validPayload());
        $operationId = $response->json('operationId');

        $auditResponse = $this->postJson('/inspection/getRequestAudit', [
            'userId' => 'user-create-001',
            'operationId' => $operationId,
        ]);

        $auditResponse->assertOk();
        $auditResponse->assertJson([
            'ok' => true,
            'audit' => [
                'userId' => 'user-create-001',
                'operationId' => $operationId,
                'operation' => 'createMember',
                'status' => 'success',
            ],
        ]);

        $events = $auditResponse->json('audit.events');
        $this->assertNotEmpty($events);
        $this->assertEquals('member_created', $events[0]['type']);
    }

    public function test_create_member_different_users_get_different_ids(): void
    {
        $first = $this->postJson('/public/createMember', $this->validPayload('user-a'));
        $second = $this->postJson('/public/createMember', $this->validPayload('user-b'));

        $this->assertNotEquals($first->json('memberId'), $second->json('memberId'));
        $this->assertNotEquals($first->json('accountId'), $second->json('accountId'));
    }
}
