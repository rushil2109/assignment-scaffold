<?php

namespace App\Providers;

use App\Contracts\AdminApiInterface;
use App\Services\MockAdminApi;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(AdminApiInterface::class, MockAdminApi::class);
    }

    public function boot(): void
    {
        //
    }
}
