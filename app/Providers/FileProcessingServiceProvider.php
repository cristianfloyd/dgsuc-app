<?php

namespace App\Providers;

use App\Livewire\CompareCuils;
use App\Livewire\SicossImporter;
use App\Services\ColumnMetadata;
use App\Services\ValidationService;
use App\Services\SicossImportService;
use App\Livewire\AfipRelacionesActivas;
use App\Services\FileProcessingService;
use Illuminate\Support\ServiceProvider;
use App\Contracts\FileProcessorInterface;
use App\Contracts\EmployeeServiceInterface;
use App\Contracts\WorkflowServiceInterface;
use App\Contracts\TransactionServiceInterface;
use App\Contracts\FileUploadRepositoryInterface;

class FileProcessingServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(FileProcessingService::class, function ($app)
        {
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
            );
        });
    }

    public function boot(): void
    {
        //
    }
}
