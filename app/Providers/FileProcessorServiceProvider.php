<?php

namespace App\Providers;

use App\services\ColumnMetadata;
use App\Services\DatabaseService;
use App\Contracts\DataMapperInterface;
use App\Services\FileProcessorService;
use Illuminate\Support\ServiceProvider;
use App\Contracts\FileProcessorInterface;

class FileProcessorServiceProvider extends ServiceProvider
{

    public function register(): void
    {
        $this->app->bind(FileProcessorInterface::class, function ($app) {
            $databaseService = $app->make(DatabaseService::class);
            $columnMetadata = $app->make(ColumnMetadata::class);
            $dataMapper = $app->make(DataMapperInterface::class);
            return new FileProcessorService($databaseService, $columnMetadata, $dataMapper);
        });
    }


    public function boot(): void
    {
        //
    }
}
