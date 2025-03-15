<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ConceptoListado\ConceptoListadoSyncService;

class SyncConceptoListado extends Command
{
    protected $signature = 'conceptos:sync';
    protected $description = 'Sincroniza los conceptos desde Mapuche';

    public function handle(ConceptoListadoSyncService $syncService)
    {
        try {
            $registros = $syncService->sync();
            $this->info("Sincronización completada: {$registros} registros procesados");
        } catch (\Exception $e) {
            $this->error("Error en la sincronización: " . $e->getMessage());
        }
    }
}
