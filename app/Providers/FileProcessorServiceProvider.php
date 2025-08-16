<?php

namespace App\Providers;

use App\Contracts\DataMapperInterface;
use App\Contracts\FileProcessorInterface;
use App\Services\ColumnMetadata;
use App\Services\DatabaseService;
use App\Services\FileProcessorService;
use Illuminate\Support\ServiceProvider;

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
    }
}
