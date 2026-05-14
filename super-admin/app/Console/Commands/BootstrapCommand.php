<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class BootstrapCommand extends Command
{
    protected $signature = 'app:bootstrap';

    protected $description = 'Run migrations and seed system state';

    public function handle(): int
    {
        Artisan::call('migrate', ['--force' => true]);
        $this->info(Artisan::output());

        DB::table('system_state')->insertOrIgnore([
            'id' => 1,
            'current_date' => '2024-01-01',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->info('Bootstrap complete.');

        return self::SUCCESS;
    }
}
