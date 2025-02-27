<?php

namespace App\Http\Controllers;

use App\Models\Dh01;
use Illuminate\Http\Request;
use App\Services\SicossCodigoActividadService;

class SicossController extends Controller
{
    protected $sicossService;

    public function __construct(SicossCodigoActividadService $sicossService)
    {
        $this->sicossService = $sicossService;
    }

    public function generarSicoss(Request $request)
    {
        // Obtener parámetros
        $anio = $request->input('anio');
        $mes = $request->input('mes');
        $nroLegajo = $request->input('nro_legajo');

        $where = $nroLegajo ? "dh01.nro_legaj = {$nroLegajo}" : 'true';

        // 1. Generar tabla temporal con conceptos liquidados
        $this->sicossService->generarTablaConceptosLiquidados($anio, $mes, $where);

        // 2. Filtrar conceptos por período (si es necesario)
        $this->sicossService->filtrarConceptosPorPeriodoRetro();

        // 3. Obtener datos del legajo desde el modelo
        $legajo = Dh01::findOrFail($nroLegajo);

        // 4. Obtener código de actividad default
        $codigoActividad = $legajo->codigoActividad;

        // 5. Obtener conceptos liquidados del legajo
        $conceptosLiquidados = $this->sicossService->obtenerConceptosLiquidados($nroLegajo);

        // 6. Calcular tipo de actividad usando esos conceptos
        $tipoActividad = $this->sicossService->calcularTipoActividad($conceptosLiquidados, $codigoActividad);

        // 7. Limpiar tablas temporales al finalizar
        $this->sicossService->limpiarTablasTemporales();

        return response()->json([
            'nroLegajo' => $nroLegajo,
            'tipoActividad' => $tipoActividad,
            'conceptosCount' => count($conceptosLiquidados)
        ]);
    }
}
