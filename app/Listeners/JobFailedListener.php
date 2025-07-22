<?php

namespace App\Listeners;

use App\Jobs\ImportAfipRelacionesActivasJob;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Support\Facades\Log;

class JobFailedListener
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {

    }

    public function handle(JobFailed $event): void
    {
        if ($event->job instanceof ImportAfipRelacionesActivasJob) {
            Log::error('Error en ImportAfipRelacionesActivasJob', [
                'exception' => $event->exception,
            ]);
        }
    }
}
