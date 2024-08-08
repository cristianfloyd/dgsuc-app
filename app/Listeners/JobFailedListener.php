<?php

namespace App\Listeners;

use Illuminate\Support\Facades\Log;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Jobs\ImportAfipRelacionesActivasJob;

class JobFailedListener
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    public function handle(JobFailed $event)
    {
        if ($event->job instanceof ImportAfipRelacionesActivasJob) {
            Log::error('Error en ImportAfipRelacionesActivasJob', [
                'exception' => $event->exception,
            ]);
        }
    }
}
