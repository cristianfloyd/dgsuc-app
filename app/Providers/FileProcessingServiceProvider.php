<?php

namespace App\Providers;

use App\Contracts\DatabaseServiceInterface;
use App\Contracts\FileProcessorInterface;
use App\Contracts\TableManagementServiceInterface;
use App\Contracts\WorkflowExecutionInterface;
use App\Contracts\WorkflowServiceInterface;
use App\Services\FileProcessingService;
use App\Services\SicossImportService;
use Illuminate\Support\ServiceProvider;

class FileProcessingServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(FileProcessingService::class, function ($app) {
            return new FileProcessingService(
                $app->make(FileProcessorInterface::class),
                $app->make(WorkflowServiceInterface::class),
                $app->make(SicossImportService::class),
                $app->make(WorkflowExecutionInterface::class),
                $app->make(DatabaseServiceInterface::class),
                $app->make(TableManagementServiceInterface::class),
            );
        });
    }

    public function boot(): void {}
}
