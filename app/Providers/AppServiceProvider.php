<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Compatibilité MySQL anciennes versions
        Schema::defaultStringLength(191);

        // Forcer HTTPS en production
        if (app()->environment('production')) {
            URL::forceScheme('https');
        }
    }
}
