<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Scripts\GenerarSicossLegajo;

class GenerarSicossCommand extends Command
{
    protected $signature = 'sicoss:generar {legajo}';
    protected $description = 'Genera un archivo SICOSS para un legajo específico';

    public function handle()
    {
        $legajo = $this->argument('legajo');
        $generador = new GenerarSicossLegajo();

        $this->info("Generando SICOSS para legajo: {$legajo}");

        $resultado = $generador->generar($legajo);

        if ($resultado['success']) {
            $this->info('SICOSS generado exitosamente');
            $this->info("Archivo: {$resultado['archivo']}");
            $this->info("ZIP: {$resultado['zip']}");
            $this->info("Resultado: " . json_encode($resultado['resultado']));
        } else {
            $this->error($resultado['message']);
        }
    }
}