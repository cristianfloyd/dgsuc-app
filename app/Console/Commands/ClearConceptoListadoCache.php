<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class ClearConceptoListadoCache extends Command
{
    protected $signature = 'concepto-listado:clear-cache';

    protected $description = 'Limpia la caché del listado de conceptos';

    public function handle(): void
    {
        $this->info('Limpiando caché...');

        try {
            // Opción 1: Limpiar caché específica
            Cache::forget('concepto_listado');

            // Opción 2: Limpiar toda la caché
            // Cache::flush();

            $this->info('Caché limpiada exitosamente!');
        } catch (\Exception $e) {
            $this->error('Error limpiando caché: ' . $e->getMessage());
        }
    }
}
