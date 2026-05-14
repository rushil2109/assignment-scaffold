<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidAllocations implements ValidationRule
{
    private const VALID_CODES = ['Cash', 'Conservative', 'Balanced', 'Growth', 'HighGrowth'];

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_array($value)) {
            $fail('The :attribute must be an array.');

            return;
        }

        $seen = [];
        $sum = '0';

        foreach ($value as $allocation) {
            if (! isset($allocation['assetCode']) || ! isset($allocation['percentage'])) {
                $fail('Each allocation must have assetCode and percentage.');

                return;
            }

            if (! in_array($allocation['assetCode'], self::VALID_CODES, true)) {
                $fail("Invalid asset code: {$allocation['assetCode']}.");

                return;
            }

            if (in_array($allocation['assetCode'], $seen, true)) {
                $fail("Duplicate asset code: {$allocation['assetCode']}.");

                return;
            }
            $seen[] = $allocation['assetCode'];

            $percentage = $allocation['percentage'];
            $parts = explode('.', (string) $percentage);
            if (isset($parts[1]) && strlen($parts[1]) > 2) {
                $fail('Percentages must have at most 2 decimal places.');

                return;
            }

            $sum = bcadd($sum, (string) $percentage, 2);
        }

        if (bccomp($sum, '100.00', 2) !== 0) {
            $fail('Allocations must sum to exactly 100.00.');
        }
    }
}
