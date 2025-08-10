<?php

namespace App\Console\Commands;

use App\Services\ConceptoListadoTableService;
use Illuminate\Console\Command;

class SyncConceptoListadoTable extends Command
{
    protected $description = 'Command description';

    protected $signature = 'concepto-listado:sync';

    public function handle(ConceptoListadoTableService $service): void
    {
        $this->info('Sincronizando tabla...');
        $service->createAndPopulate();
        $this->info('Sincronización completada');
    }
}
