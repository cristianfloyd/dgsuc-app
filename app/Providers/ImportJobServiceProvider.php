<?php

namespace App\Providers;

use App\Services\ColumnMetadata;
use App\Services\EmployeeService;
use App\Services\ValidationService;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\ServiceProvider;
use App\Contracts\FileProcessorInterface;
use App\Contracts\WorkflowServiceInterface;
use App\Jobs\ImportAfipRelacionesActivasJob;
use App\Contracts\TransactionServiceInterface;
use App\Jobs\Middleware\InspectJobDependencies;

class ImportJobServiceProvider extends ServiceProvider
{
    public function register()
{
    // Registramos las dependencias para ImportAfipRelacionesActivasJob
    $this->app->when(ImportAfipRelacionesActivasJob::class)
        ->needs('$fileProcessor')
        ->give(function ($app) {
            /// Devuelve una instancia del servicio FileProcessorInterface, que se utiliza para procesar archivos.
            return $app->make(FileProcessorInterface::class);
        });

    $this->app->when(ImportAfipRelacionesActivasJob::class)
        ->needs('$employeeService')
        ->give(function ($app) {
            return $app->make(EmployeeService::class);
        });

    $this->app->when(ImportAfipRelacionesActivasJob::class)
        ->needs('$validationService')
        ->give(function ($app) {
            return $app->make(ValidationService::class);
        });

    $this->app->when(ImportAfipRelacionesActivasJob::class)
        ->needs('$transactionService')
        ->give(function ($app) {
            return $app->make(TransactionServiceInterface::class);
        });

    $this->app->when(ImportAfipRelacionesActivasJob::class)
        ->needs('$workflowService')
        ->give(function ($app) {
            return $app->make(WorkflowServiceInterface::class);
        });

    $this->app->when(ImportAfipRelacionesActivasJob::class)
        ->needs('$columnMetadata')
        ->give(function ($app) {
            return $app->make(ColumnMetadata::class);
        });

    // Registramos el job en el contenedor de servicios
    $this->app->bind(ImportAfipRelacionesActivasJob::class, function ($app, $parameters) {
        return new ImportAfipRelacionesActivasJob(
            $app->make(FileProcessorInterface::class),
            $app->make(EmployeeService::class),
            $app->make(ValidationService::class),
            $app->make(TransactionServiceInterface::class),
            $app->make(WorkflowServiceInterface::class),
            $app->make(ColumnMetadata::class),
            $parameters['uploadedFileId'],
        );
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
