<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ConceptoListadoResourceService;

class RefreshConceptoListadoView extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'concepto-listado:refresh';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refresca la vista materializada de concepto_listado';

    /**
     * Execute the console command.
     */
    public function handle(ConceptoListadoResourceService $service)
    {
        $this->info('Refrescando vista materializada...');
        $service->refreshMaterializedView();
        $this->info('Vista materializada actualizada exitosamente!');
    }
}
