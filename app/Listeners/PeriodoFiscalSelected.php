<?php

namespace App\Listeners;

use App\Events\PeriodoFiscalActualizado;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class PeriodoFiscalSelected
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
    public function handle(PeriodoFiscalActualizado $event): void
    {
        //
    }
}
