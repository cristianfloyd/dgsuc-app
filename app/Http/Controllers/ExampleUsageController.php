<?php

namespace App\Http\Controllers;

use App\Services\Reportes\ConceptosTotalesService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExampleUsageController extends Controller
{
    /**
     * @param ConceptosTotalesService $conceptosTotalesService
     */
    public function __construct(
        private readonly ConceptosTotalesService $conceptosTotalesService
    ) {}

    /**
     * Ejemplo de uso del servicio de conceptos totales
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getConceptosTotales(Request $request): JsonResponse
    {
        // Validar parámetros
        $request->validate([
            'year' => 'required|integer|min:2020|max:2030',
            'month' => 'required|integer|min:1|max:12',
            'conceptos' => 'nullable|array',
            'conceptos.*' => 'string'
        ]);

        // Obtener parámetros
        $year = $request->input('year');
        $month = $request->input('month');
        $conceptos = $request->input('conceptos');

        // Obtener el reporte de conceptos
        $reporte = $this->conceptosTotalesService->getReporteConceptos($year, $month, $conceptos);

        // Devolver respuesta JSON
        return response()->json([
            'data' => $reporte,
            'meta' => [
                'year' => $year,
                'month' => $month,
                'total_haberes' => $reporte->totalHaberes,
                'total_descuentos' => $reporte->totalDescuentos,
                'neto' => $reporte->neto
            ]
        ]);
    }
}
