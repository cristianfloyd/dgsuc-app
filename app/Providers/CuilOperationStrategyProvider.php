<?php

namespace App\Providers;

use App\PaginationConstants;
use Illuminate\Support\ServiceProvider;
use App\Contracts\CuilOperationStrategy;
use App\Strategies\CompareCuilsStrategy;
use App\Contracts\CuilRepositoryInterface;
use App\Contracts\WorkflowServiceInterface;
use App\Strategies\LoadCuilsNotInAfipStrategy;

class CuilOperationStrategyProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(CuilOperationStrategy::class, function ($app) {

            $strategy = $app['config']->get('cuil_operation_strategy');

            if ($strategy === 'compare_cuils') {
                return new CompareCuilsStrategy(
                    $app->make(CuilRepositoryInterface::class),
                    PaginationConstants::PER_PAGE
                );
            } elseif ($strategy === 'load_cuils_not_in_afip') {
                return new LoadCuilsNotInAfipStrategy(
                    $app->make(CuilRepositoryInterface::class),
                    $app->make(WorkflowServiceInterface::class),
                    $app->make('processLog')
                );
            }
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
