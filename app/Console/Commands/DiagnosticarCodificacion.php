<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Reportes\OrdenesDescuento;

class DiagnosticarCodificacion extends Command
{
    protected $signature = 'diagnostico:codificacion {id}';
    protected $description = 'Diagnostica problemas de codificación para un registro específico';

    public function handle()
    {
        $id = $this->argument('id');
        $diagnostico = OrdenesDescuento::diagnosticarCodificacion($id);

        if (isset($diagnostico['error'])) {
            $this->error($diagnostico['error']);
            return 1;
        }

        $this->info('Diagnóstico de codificación:');
        $this->table(
            ['Campo', 'Encoding Detectado', 'Longitud', 'Valor UTF-8'],
            collect($diagnostico['campos'])->map(function ($info, $campo) {
                return [
                    $campo,
                    $info['encoding_detectado'] ?? 'N/A',
                    $info['longitud'] ?? 'N/A',
                    $info['valor_utf8'] ?? 'N/A',
                ];
            })
        );

        $this->info('Configuración de base de datos:');
        $this->table(
            ['Configuración', 'Valor'],
            collect($diagnostico['configuracion_db'])->map(function ($valor, $clave) {
                return [$clave, $valor];
            })
        );

        return 0;
    }
}
