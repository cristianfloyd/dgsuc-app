<?php

namespace App\Providers;

use App\Services\FileProcessorService;
use Illuminate\Support\ServiceProvider;

class FileProcesorServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(FileProcessorService::class, function () {
            return new FileProcessorService();
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
