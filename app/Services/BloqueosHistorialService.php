<?php

declare(strict_types=1);

namespace App\Services;

use Carbon\Carbon;
use App\Models\BloqueosHistorial;
use Illuminate\Support\Collection;

class BloqueosHistorialService
{
    public function registrarCambio(array $datos): void
    {
        BloqueosHistorial::create([
            'periodo_importacion' => Carbon::now()->startOfMonth(),
            ...$datos,
            'metadata' => [
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent()
            ]
        ]);
    }

    public function obtenerEstadisticasPeriodo(string $periodo): array
    {
        $registros = BloqueosHistorial::where('periodo_importacion', $periodo)
            ->get();

        return [
            'total_registros' => $registros->count(),
            'procesados_ok' => $registros->where('resultado_final', true)->count(),
            'procesados_error' => $registros->where('resultado_final', false)->count(),
            'porcentaje_exito' => $this->calcularPorcentajeExito($registros)
        ];
    }

    private function calcularPorcentajeExito(Collection $registros): float
    {
        $total = $registros->count();
        if ($total === 0) return 0.0;

        return round(
            ($registros->where('resultado_final', true)->count() * 100) / $total,
            2
        );
    }
}
