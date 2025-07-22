<?php

namespace App\Providers;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\ServiceProvider;

class ScheduleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->booted(function (): void {
            $schedule = $this->app->make(Schedule::class);
            $schedule->command('concepto-listado:refresh')
                ->monthlyOn(1, '00:01')
                ->withoutOverLapping();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {

    }
}
