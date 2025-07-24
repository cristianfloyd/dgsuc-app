<?php

namespace App\Providers;

use App\Auth\TobaGuard;
use App\Auth\TobaUserProvider;
use App\Services\TobaAuthService;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Auth;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Registrar el provider personalizado
        Auth::provider('toba', function ($app, array $config) {
            return new TobaUserProvider($app->make(TobaAuthService::class));
        });

        // Registrar el guard personalizado
        Auth::extend('toba', function ($app, $name, array $config) {
            return new TobaGuard(
                $name,
                Auth::createUserProvider($config['provider']),
                $app['session.store']
            );
        });
    }
}
