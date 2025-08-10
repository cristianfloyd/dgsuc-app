<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Reportes\FallecidoService;
use Illuminate\Console\Command;

class RefreshFallecidosData extends Command
{
    protected $signature = 'fallecidos:refresh';

    protected $description = 'Actualiza los datos de la tabla rep_fallecidos desde Mapuche';

    public function handle(FallecidoService $service): int
    {
        $this->info('Iniciando actualización de datos de fallecidos...');

        try {
            $service->refreshData();
            $this->info('Datos actualizados exitosamente.');
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Error al actualizar datos: {$e->getMessage()}");
            return Command::FAILURE;
        }
    }
}
