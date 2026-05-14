<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CleanCommand extends Command
{
    protected $signature = 'app:clean';

    protected $description = 'Truncate all tables and reset system state';

    public function handle(): int
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        $tables = [
            'audit_events',
            'audit_operations',
            'holdings',
            'unit_prices',
            'transactions',
            'investment_profiles',
            'accounts',
            'members',
            'system_state',
        ];

        foreach ($tables as $table) {
            DB::table($table)->truncate();
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        DB::table('system_state')->insert([
            'id' => 1,
            'current_date' => '2024-01-01',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->info('All tables truncated. System state reset to 2024-01-01.');

        return self::SUCCESS;
    }
}
