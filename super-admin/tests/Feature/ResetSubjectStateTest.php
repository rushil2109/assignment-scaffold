<?php

namespace Tests\Feature;

use App\Models\AuditEvent;
use App\Models\AuditOperation;
use App\Models\Member;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ResetSubjectStateTest extends TestCase
{
    use RefreshDatabase;

    private function createMemberPayload(string $userId = 'user-reset-001'): array
    {
        return [
            'userId' => $userId,
            'firstName' => 'Test',
            'lastName' => 'User',
            'email' => 'reset@example.com',
            'mobile' => '0400000000',
            'dateOfBirth' => '1990-01-01',
            'initialInvestmentProfile' => [
                ['assetCode' => 'Growth', 'percentage' => 60],
                ['assetCode' => 'Cash', 'percentage' => 40],
            ],
        ];
    }

    private function createMemberViaPublicApi(string $userId = 'user-reset-001'): array
    {
        $response = $this->postJson('/public/createMember', $this->createMemberPayload($userId));
        $response->assertOk();

        return $response->json();
    }

    public function test_reset_deletes_all_vendor_data(): void
    {
        $result = $this->createMemberViaPublicApi();

        $account = Member::where('user_id', 'user-reset-001')->first()->account;
        $this->postJson('/mock/addTransactions', [
            'userId' => 'user-reset-001',
            'accountId' => $account->account_id,
            'transactions' => [
                ['type' => 'contribution', 'amount' => '1000.00', 'effectiveDate' => '2024-01-15'],
            ],
        ]);

        $this->assertDatabaseHas('members', ['user_id' => 'user-reset-001']);
        $this->assertDatabaseCount('transactions', 1);

        $response = $this->postJson('/mock/resetSubjectState', [
            'userId' => 'user-reset-001',
        ]);

        $response->assertOk();
        $response->assertJson(['ok' => true]);

        $this->assertDatabaseMissing('members', ['user_id' => 'user-reset-001']);
        $this->assertDatabaseCount('accounts', 0);
        $this->assertDatabaseCount('transactions', 0);
        $this->assertDatabaseCount('investment_profiles', 0);
        $this->assertDatabaseCount('holdings', 0);
    }

    public function test_audit_preserved_after_reset(): void
    {
        $this->createMemberViaPublicApi();

        $this->assertDatabaseHas('audit_operations', ['user_id' => 'user-reset-001']);
        $auditCount = AuditOperation::where('user_id', 'user-reset-001')->count();
        $eventCount = AuditEvent::count();

        $this->assertGreaterThan(0, $auditCount);
        $this->assertGreaterThan(0, $eventCount);

        $this->postJson('/mock/resetSubjectState', ['userId' => 'user-reset-001']);

        $this->assertDatabaseHas('audit_operations', ['user_id' => 'user-reset-001']);
        $this->assertEquals($auditCount, AuditOperation::where('user_id', 'user-reset-001')->count());
        $this->assertEquals($eventCount, AuditEvent::count());
    }

    public function test_create_member_works_after_reset(): void
    {
        $first = $this->createMemberViaPublicApi();

        $this->postJson('/mock/resetSubjectState', ['userId' => 'user-reset-001']);
        $this->assertDatabaseMissing('members', ['user_id' => 'user-reset-001']);

        $second = $this->createMemberViaPublicApi();

        $this->assertTrue($second['ok']);
        $this->assertNotEquals($first['memberId'], $second['memberId']);
    }

    public function test_reset_nonexistent_user_returns_ok(): void
    {
        $response = $this->postJson('/mock/resetSubjectState', [
            'userId' => 'user-does-not-exist',
        ]);

        $response->assertOk();
        $response->assertJson(['ok' => true]);
    }

    public function test_reset_creates_no_audit_rows(): void
    {
        $this->createMemberViaPublicApi();
        $auditCountBefore = AuditOperation::count();
        $eventCountBefore = AuditEvent::count();

        $this->postJson('/mock/resetSubjectState', ['userId' => 'user-reset-001']);

        $this->assertEquals($auditCountBefore, AuditOperation::count());
        $this->assertEquals($eventCountBefore, AuditEvent::count());
    }
}
