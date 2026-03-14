<?php

namespace App\Providers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        if ($this->app->hasDebugModeEnabled()) {
            DB::listen(function ($builder) {
                Log::debug($builder->sql);
            });
        }
    }
}
