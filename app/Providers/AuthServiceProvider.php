<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Auth\TobaUserProvider;
use Illuminate\Support\Facades\Auth;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->registerPolicies();

        Auth::provider('toba', function ($app, array $config) {
            return new TobaUserProvider($app['hash'], $config['model']);
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
