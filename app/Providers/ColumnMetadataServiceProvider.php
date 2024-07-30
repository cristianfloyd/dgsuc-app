<?php

namespace App\Providers;

use App\services\ColumnMetadata;
use Illuminate\Support\ServiceProvider;

class ColumnMetadataServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(ColumnMetadata::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
