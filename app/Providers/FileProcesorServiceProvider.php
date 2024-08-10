<?php

namespace App\Providers;

use App\services\ColumnMetadata;
use App\Services\DatabaseService;
use App\Services\FileProcessorService;
use Illuminate\Support\ServiceProvider;
use App\Contracts\FileProcessorInterface;

class FileProcesorServiceProvider extends ServiceProvider
{

    public function register(): void
    {
        $this->app->bind(FileProcessorInterface::class, function ($app) {
            $databaseService = $app->make(DatabaseService::class);
            return new FileProcessorService($databaseService);
        });
    }


    public function boot(): void
    {
        //
    }
}
