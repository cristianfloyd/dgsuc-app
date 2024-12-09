<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ConceptoListadoTableService;

class SyncConceptoListadoTable extends Command
{
    protected $description = 'Command description';

    protected $signature = 'concepto-listado:sync';

    public function handle(ConceptoListadoTableService $service)
    {
        $this->info('Sincronizando tabla...');
        $service->createAndPopulate();
        $this->info('Sincronización completada');
    }
}
