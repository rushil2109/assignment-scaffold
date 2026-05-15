<?php

namespace Tests\Unit;

use App\Contracts\AdminApiInterface;
use App\Models\Holding;
use App\Models\Transaction;
use App\Models\UnitPrice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DoubleProcessingGuardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        DB::table('system_state')->updateOrInsert(
            ['id' => 1],
            ['current_date' => '2024-01-01', 'created_at' => now(), 'updated_at' => now()]
        );
    }

    public function test_second_move_day_forward_produces_no_duplicate_rows(): void
    {
        $api = app(AdminApiInterface::class);
        $member = $api->createMember('admin-dbl-001', [
            'userId' => 'user-dbl',
            'email' => 'dbl@example.com',
        ]);

        $api->setInvestmentProfile('admin-dbl-001', [
            'allocations' => [
                ['assetCode' => 'Cash', 'percentage' => 60],
                ['assetCode' => 'Growth', 'percentage' => 40],
            ],
        ]);

        $account = DB::table('accounts')->where('account_id', $member['accountId'])->first();

        Transaction::create([
            'id' => 'txn-dbl-001',
            'account_id' => $account->id,
            'type' => 'contribution',
            'amount' => '500.00',
            'effective_date' => '2024-01-02',
        ]);

        UnitPrice::updateOrCreate(['asset_code' => 'Cash', 'date' => '2024-01-02'], ['price' => '1.000000']);
        UnitPrice::updateOrCreate(['asset_code' => 'Growth', 'date' => '2024-01-02'], ['price' => '2.000000']);

        // First call — processes day 2024-01-02
        $response1 = $this->postJson('/mock/moveDayForward', ['days' => 1]);
        $response1->assertJson(['ok' => true, 'processedDates' => ['2024-01-02']]);

        $holdingsCountAfterFirst = Holding::where('account_id', $account->id)
            ->where('effective_date', '2024-01-02')
            ->count();

        // Second call — advances to 2024-01-03 (guard: system_state already at 2024-01-02)
        // No prices set for 2024-01-03, but the system still advances
        UnitPrice::updateOrCreate(['asset_code' => 'Cash', 'date' => '2024-01-03'], ['price' => '1.000000']);
        UnitPrice::updateOrCreate(['asset_code' => 'Growth', 'date' => '2024-01-03'], ['price' => '2.000000']);

        $response2 = $this->postJson('/mock/moveDayForward', ['days' => 1]);
        $response2->assertJson(['ok' => true]);

        // Verify no duplicate holdings for 2024-01-02
        $holdingsCountAfterSecond = Holding::where('account_id', $account->id)
            ->where('effective_date', '2024-01-02')
            ->count();

        $this->assertEquals(
            $holdingsCountAfterFirst,
            $holdingsCountAfterSecond,
            'Second moveDayForward must not create duplicate rows for already-processed date'
        );
    }
}
