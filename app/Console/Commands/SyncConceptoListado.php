<?php

namespace App\Console\Commands;

use App\Services\ConceptoListado\ConceptoListadoSyncService;
use Exception;
use Illuminate\Console\Command;

class SyncConceptoListado extends Command
{
    protected $signature = 'conceptos:sync';

    protected $description = 'Sincroniza los conceptos desde Mapuche';

    public function handle(ConceptoListadoSyncService $syncService): void
    {
        try {
            $registros = $syncService->sync();
            $this->info("Sincronización completada: {$registros} registros procesados");
        } catch (Exception $e) {
            $this->error('Error en la sincronización: ' . $e->getMessage());
        }
    }
}
