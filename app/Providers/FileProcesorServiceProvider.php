<?php

namespace App\Providers;

use App\services\ColumnMetadata;
use App\Services\FileProcessorService;
use Illuminate\Support\ServiceProvider;
use App\Contracts\FileProcessorInterface;

class FileProcesorServiceProvider extends ServiceProvider
{

    public function register(): void
    {
        $this->app->bind(FileProcessorInterface::class, function ($app) {
            return new FileProcessorService();
        });
    }


    public function boot(): void
    {
        //
    }
}
