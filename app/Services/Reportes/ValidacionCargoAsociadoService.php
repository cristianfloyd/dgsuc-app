<?php

namespace App\Services\Reportes;

use App\Models\Reportes\BloqueosDataModel;
use Illuminate\Support\Facades\Log;

class ValidacionCargoAsociadoService
{
    /**
     * Verifica si todos los registros tienen cargo asociado en Mapuche.
     *
     * @return array Estadísticas del proceso
     */
    public function validarCargosAsociados(): array
    {
        $registros = BloqueosDataModel::all();
        $total = $registros->count();
        $conCargo = 0;
        $sinCargo = 0;

        foreach ($registros as $registro) {
            try {
                $registro->verificarCargoAsociado();

                if ($registro->tiene_cargo_asociado) {
                    $conCargo++;
                } else {
                    $sinCargo++;
                }
            } catch (\Exception $e) {
                Log::error("Error al validar cargo asociado: {$e->getMessage()}", [
                    'legajo' => $registro->nro_legaj,
                    'cargo' => $registro->nro_cargo,
                ]);
                $sinCargo++;
            }
        }

        return [
            'total' => $total,
            'con_cargo' => $conCargo,
            'sin_cargo' => $sinCargo,
        ];
    }
}
