<?php

namespace App\Providers;

use App\Auth\TobaUserProvider;
use App\Services\TobaApiService;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Auth;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // $this->registerPolicies();

        Auth::provider('toba', function ($app, array $config) {
            return new TobaUserProvider(
                $app->make(TobaApiService::class),
                $app['hash'],
                $config['model'],
            );
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Gate::before(function (User $user, string $ability) {
        //     return $user->isSuperAdmin() ? true: null;
        // });
    }
}
