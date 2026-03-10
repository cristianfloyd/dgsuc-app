<?php

namespace App\Listeners;

use App\Jobs\ImportAfipRelacionesActivasJob;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Support\Facades\Log;

class JobProcessedListener
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
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
