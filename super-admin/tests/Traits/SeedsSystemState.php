<?php

namespace Tests\Traits;

use Illuminate\Support\Facades\DB;

trait SeedsSystemState
{
    protected function seedSystemState(string $date = '2024-01-01'): void
    {
        DB::table('system_state')->insert([
            'id' => 1,
            'current_date' => $date,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
