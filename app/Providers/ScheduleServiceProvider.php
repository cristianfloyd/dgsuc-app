<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Console\Scheduling\Schedule;

class ScheduleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->booted(function (){
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
        //
    }
}
