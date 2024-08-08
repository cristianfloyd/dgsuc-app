<?php

namespace App\Listeners;

use App\Jobs\ImportAfipRelacionesActivasJob;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Contracts\Queue\ShouldQueue;

class JobProcessedListener
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(JobProcessed $event): void
    {

        if ($event->job instanceof ImportAfipRelacionesActivasJob) {
            Log::info('Job ImportAfipRelacionesActivasJob completado exitosamente');
        }

    }
}
