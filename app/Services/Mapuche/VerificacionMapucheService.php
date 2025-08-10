<?php

declare(strict_types=1);

namespace App\Services\Mapuche;

use App\Models\Dh03;

class VerificacionMapucheService
{
    public function verificarLegajoCargo(int $nroLegaj, int $nroCargo): array
    {
        $cargo = Dh03::with('dh01')
            ->where('nro_legaj', $nroLegaj)
            ->where('nro_cargo', $nroCargo)
            ->first();

        if (!$cargo) {
            return [
                'existe' => false,
                'mensaje' => "No se encontró el cargo $nroCargo para el legajo $nroLegaj",
            ];
        }

        return [
            'existe' => true,
            'datos' => [
                'legajo' => $cargo->nro_legaj,
                'cargo' => $cargo->nro_cargo,
                'nombre' => $cargo->dh01->nombre_completo,
                'fecha_alta' => $cargo->fec_alta?->format('d/m/Y'),
                'fecha_baja' => $cargo->fec_baja?->format('d/m/Y'),
                'estado' => $cargo->chkstopliq ? 'Bloqueado' : 'Activo',
            ],
        ];
    }
}
