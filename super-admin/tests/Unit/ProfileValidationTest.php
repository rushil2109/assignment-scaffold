<?php

namespace Tests\Unit;

use App\Rules\ValidAllocations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class ProfileValidationTest extends TestCase
{
    use RefreshDatabase;

    private function validateAllocations(array $allocations): bool
    {
        $validator = Validator::make(
            ['allocations' => $allocations],
            ['allocations' => ['required', 'array', new ValidAllocations]]
        );

        return $validator->passes();
    }

    public function test_sum_not_100_rejected(): void
    {
        $result = $this->validateAllocations([
            ['assetCode' => 'Cash', 'percentage' => 50],
            ['assetCode' => 'Growth', 'percentage' => 49],
        ]);

        $this->assertFalse($result, 'Allocations summing to 99 should be rejected');
    }

    public function test_invalid_asset_code_rejected(): void
    {
        $result = $this->validateAllocations([
            ['assetCode' => 'InvalidCode', 'percentage' => 100],
        ]);

        $this->assertFalse($result, 'Invalid asset code should be rejected');
    }

    public function test_duplicate_asset_code_rejected(): void
    {
        $result = $this->validateAllocations([
            ['assetCode' => 'Cash', 'percentage' => 50],
            ['assetCode' => 'Cash', 'percentage' => 50],
        ]);

        $this->assertFalse($result, 'Duplicate asset codes should be rejected');
    }

    public function test_excess_decimal_places_rejected(): void
    {
        $result = $this->validateAllocations([
            ['assetCode' => 'Cash', 'percentage' => 33.333],
            ['assetCode' => 'Growth', 'percentage' => 66.667],
        ]);

        $this->assertFalse($result, 'More than 2 decimal places should be rejected');
    }

    public function test_valid_allocations_accepted(): void
    {
        $result = $this->validateAllocations([
            ['assetCode' => 'Cash', 'percentage' => 33.33],
            ['assetCode' => 'Growth', 'percentage' => 33.33],
            ['assetCode' => 'Balanced', 'percentage' => 33.34],
        ]);

        $this->assertTrue($result, 'Valid allocations summing to 100 should pass');
    }
}
