<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->trustProxies(at: '*');
        // Comentamos temporalmente el middleware propio
        // $middleware->web(append: [
        //     \App\Http\Middleware\SetDatabaseConnection::class,
        // ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })
    // Desactivamos temporalmente todos los providers propios de la aplicación
    ->withProviders([
        // Solo mantenemos los providers de Laravel
        // Comentamos todos los providers propios
        // \App\Providers\EmbargoServiceProvider::class,
        // \App\Providers\TableVerificationServiceProvider::class,
        // \App\Providers\TransactionServiceProvider::class,
        // \App\Providers\ValidationServiceProvider::class,
        // \App\Providers\WorkflowServiceProvider::class,
        // \App\Providers\ScheduleServiceProvider::class,
        // \App\Providers\FilamentServiceProvider::class,
        // \App\Providers\FileProcessingServiceProvider::class,
        // \App\Providers\FileProcessorServiceProvider::class,
        // \App\Providers\FortifyServiceProvider::class,
        // \App\Providers\ImportJobServiceProvider::class,
        // \App\Providers\ImportServiceProvider::class,
        // \App\Providers\JetstreamServiceProvider::class,
        // \App\Providers\MapucheMiSimplificacionProvider::class,
        // \App\Providers\MessageManagerProvider::class,
        // \App\Providers\RepositoryServiceProvider::class,
        // \App\Providers\EmployeeRepositoryProvider::class,
        // \App\Providers\ConceptoListadoServiceProvider::class,
        // \App\Providers\CuilOperationStrategyProvider::class,
        // \App\Providers\CuilsRepositoryProvider::class,
        // \App\Providers\DatabaseServiceProvider::class,
        // \App\Providers\DataMapperProvider::class,
        // \App\Providers\AfipMapucheArtProvider::class,
        // \App\Providers\AfipMapucheSicossProvider::class,
        // \App\Providers\AfipMapucheSicossServiceProvider::class,
        // \App\Providers\AfipProvider::class,
        // \App\Providers\AppServiceProvider::class,
        // \App\Providers\AuthServiceProvider::class,
        // \App\Providers\CargoFilterServiceProvider::class,
        // \App\Providers\ColumnMetadataServiceProvider::class,
    ])
    ->create();
