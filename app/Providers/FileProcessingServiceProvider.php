<?php

namespace App\Providers;

use App\Contracts\DatabaseServiceInterface;
use App\Contracts\EmployeeServiceInterface;
use App\Contracts\FileProcessorInterface;
use App\Contracts\FileUploadRepositoryInterface;
use App\Contracts\TableManagementServiceInterface;
use App\Contracts\TransactionServiceInterface;
use App\Contracts\WorkflowExecutionInterface;
use App\Contracts\WorkflowServiceInterface;
use App\Livewire\AfipRelacionesActivas;
use App\Livewire\CompareCuils;
use App\Livewire\SicossImporter;
use app\Services\ColumnMetadata;
use App\Services\FileProcessingService;
use App\Services\SicossImportService;
use App\Services\ValidationService;
use Illuminate\Support\ServiceProvider;

class FileProcessingServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(FileProcessingService::class, function ($app) {
            return new FileProcessingService(
                $app->make(AfipRelacionesActivas::class),
                $app->make(SicossImporter::class),
                $app->make(CompareCuils::class),
                $app->make(FileUploadRepositoryInterface::class),
                $app->make(FileProcessorInterface::class),
                $app->make(EmployeeServiceInterface::class),
                $app->make(ValidationService::class),
                $app->make(TransactionServiceInterface::class),
                $app->make(WorkflowServiceInterface::class),
                $app->make(ColumnMetadata::class),
                $app->make(SicossImportService::class),
                $app->make(WorkflowExecutionInterface::class),
                $app->make(DatabaseServiceInterface::class),
                $app->make(TableManagementServiceInterface::class),
            );
        });
    }

    public function boot(): void
    {
    }
}
