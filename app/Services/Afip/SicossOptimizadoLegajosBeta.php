<?php

namespace App\Services\Afip;

class SicossOptimizadoLegajosBeta
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {

    }

    /**
     * Método auxiliar para obtener estadísticas de la pre-carga.
     *
     * @param array $todos_conceptos Array resultado de precargar_conceptos_todos_legajos
     * @param array $legajos Array original de legajos
     *
     * @return array Estadísticas útiles para debugging
     */
    public static function obtener_estadisticas_precarga($todos_conceptos, $legajos): array
    {
        $stats = [
            'total_conceptos' => \count($todos_conceptos),
            'total_legajos_solicitados' => \count($legajos),
            'legajos_con_conceptos' => 0,
            'legajos_sin_conceptos' => 0,
            'conceptos_por_legajo' => [],
            'memoria_utilizada_mb' => memory_get_usage(true) / 1024 / 1024,
        ];

        // Agrupar por legajo para estadísticas
        $conceptos_agrupados = [];
        foreach ($todos_conceptos as $concepto) {
            $nro_legaj = $concepto['nro_legaj'];
            if (!isset($conceptos_agrupados[$nro_legaj])) {
                $conceptos_agrupados[$nro_legaj] = 0;
            }
            $conceptos_agrupados[$nro_legaj]++;
        }

        // Calcular estadísticas
        $legajos_solicitados = array_column($legajos, 'nro_legaj');
        foreach ($legajos_solicitados as $legajo) {
            if (isset($conceptos_agrupados[$legajo])) {
                $stats['legajos_con_conceptos']++;
                $stats['conceptos_por_legajo'][] = $conceptos_agrupados[$legajo];
            } else {
                $stats['legajos_sin_conceptos']++;
                $stats['conceptos_por_legajo'][] = 0;
            }
        }

        // Estadísticas adicionales
        if (!empty($stats['conceptos_por_legajo'])) {
            $stats['promedio_conceptos_por_legajo'] = array_sum($stats['conceptos_por_legajo']) / \count($stats['conceptos_por_legajo']);
            $stats['max_conceptos_por_legajo'] = max($stats['conceptos_por_legajo']);
            $stats['min_conceptos_por_legajo'] = min($stats['conceptos_por_legajo']);
        }

        return $stats;
    }
}
