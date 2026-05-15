<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InspectionApiTest extends TestCase
{
    use RefreshDatabase;

    private function createMember(string $userId = 'user-inspect-001'): array
    {
        $response = $this->postJson('/public/createMember', [
            'userId' => $userId,
            'firstName' => 'Inspect',
            'lastName' => 'Test',
            'email' => 'inspect@example.com',
            'mobile' => '0400000000',
            'dateOfBirth' => '1990-01-01',
            'initialInvestmentProfile' => [
                ['assetCode' => 'Growth', 'percentage' => 100],
            ],
        ]);

        return $response->json();
    }

    public function test_get_request_audit_structure(): void
    {
        $member = $this->createMember();
        $operationId = $member['operationId'];

        $response = $this->postJson('/inspection/getRequestAudit', [
            'userId' => 'user-inspect-001',
            'operationId' => $operationId,
        ]);

        $response->assertOk();
        $response->assertJson(['ok' => true]);

        $audit = $response->json('audit');
        $this->assertArrayHasKey('userId', $audit);
        $this->assertArrayHasKey('operationId', $audit);
        $this->assertArrayHasKey('operation', $audit);
        $this->assertArrayHasKey('status', $audit);
        $this->assertArrayHasKey('events', $audit);

        $this->assertEquals('user-inspect-001', $audit['userId']);
        $this->assertEquals($operationId, $audit['operationId']);
        $this->assertEquals('createMember', $audit['operation']);
        $this->assertEquals('success', $audit['status']);

        $this->assertNotEmpty($audit['events']);
        $event = $audit['events'][0];
        $this->assertArrayHasKey('at', $event);
        $this->assertArrayHasKey('type', $event);
        $this->assertArrayHasKey('details', $event);
    }

    public function test_get_request_audit_not_found(): void
    {
        $response = $this->postJson('/inspection/getRequestAudit', [
            'userId' => 'user-inspect-001',
            'operationId' => 'nonexistent-operation-id',
        ]);

        $response->assertOk();
        $response->assertJson(['ok' => false]);
    }

    public function test_list_audit_events_ordering(): void
    {
        $member = $this->createMember();

        // Second mutation
        $this->postJson('/public/updateMember', [
            'userId' => 'user-inspect-001',
            'memberId' => $member['memberId'],
            'email' => 'updated@example.com',
        ]);

        // Third mutation
        $this->postJson('/public/setInvestmentProfile', [
            'userId' => 'user-inspect-001',
            'memberId' => $member['memberId'],
            'accountId' => $member['accountId'],
            'allocations' => [
                ['assetCode' => 'Cash', 'percentage' => 100],
            ],
        ]);

        $response = $this->postJson('/inspection/listAuditEvents', [
            'userId' => 'user-inspect-001',
        ]);

        $response->assertOk();
        $response->assertJson(['ok' => true]);

        $events = $response->json('events');
        $this->assertGreaterThanOrEqual(3, count($events));

        // Verify ordering: events from createMember should come before updateMember before setInvestmentProfile
        $types = array_column($events, 'type');
        $createIdx = array_search('member_created', $types);
        $updateIdx = array_search('member_updated', $types);
        $profileIdx = array_search('profile_updated', $types);

        $this->assertLessThan($updateIdx, $createIdx);
        $this->assertLessThan($profileIdx, $updateIdx);
    }

    public function test_list_audit_events_contains_correct_structure(): void
    {
        $this->createMember();

        $response = $this->postJson('/inspection/listAuditEvents', [
            'userId' => 'user-inspect-001',
        ]);

        $events = $response->json('events');
        $event = $events[0];

        $this->assertArrayHasKey('at', $event);
        $this->assertArrayHasKey('type', $event);
        $this->assertArrayHasKey('details', $event);
    }

    public function test_list_audit_events_empty_for_unknown_user(): void
    {
        $response = $this->postJson('/inspection/listAuditEvents', [
            'userId' => 'user-nobody',
        ]);

        $response->assertOk();
        $response->assertJson(['ok' => true, 'events' => []]);
    }

    public function test_read_operations_do_not_emit_audit(): void
    {
        $member = $this->createMember();

        $this->postJson('/public/getInvestmentPortfolio', [
            'userId' => 'user-inspect-001',
            'memberId' => $member['memberId'],
            'accountId' => $member['accountId'],
        ]);

        $response = $this->postJson('/inspection/listAuditEvents', [
            'userId' => 'user-inspect-001',
        ]);

        $events = $response->json('events');
        // Only the createMember event should exist, not getInvestmentPortfolio
        $types = array_column($events, 'type');
        $this->assertNotContains('get_investment_portfolio', $types);
    }
}
