<?php

namespace App\Jobs;

use App\Services\MaterializedView\ConceptoListadoViewService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RefreshMaterializedViewJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public $tries = 3;

    public $timeout = 6000;

    public function handle(ConceptoListadoViewService $service): void
    {
        $service->refresh();
    }
}
