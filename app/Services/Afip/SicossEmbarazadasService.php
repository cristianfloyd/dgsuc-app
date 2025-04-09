<?php

declare(strict_types=1);

namespace App\Services\Afip;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Traits\MapucheConnectionTrait;
use App\Services\Mapuche\PeriodoFiscalService;
use App\Services\Mapuche\TableSelectorService;

class SicossEmbarazadasService
{
    use MapucheConnectionTrait;

    protected TableSelectorService $tableSelectorService;
    protected PeriodoFiscalService $periodoFiscalService;

    public function __construct(
        TableSelectorService $tableSelectorService,
        PeriodoFiscalService $periodoFiscalService
    ) {
        $this->tableSelectorService = $tableSelectorService;
        $this->periodoFiscalService = $periodoFiscalService;
    }

    /**
     * Actualiza los datos de embarazadas en SICOSS
     *
     * @param array $params Parámetros para la actualización
     * @return array Resultado de la operación
     */
    public function actualizarEmbarazadas(array $params = []): array
    {
        $periodoFiscal = $this->periodoFiscalService->getPeriodoFiscal();
        $year = $params['year'] ?? $periodoFiscal['year'];
        $month = $params['month'] ?? $periodoFiscal['month'];
        
        // Calcular fechas de inicio y fin del mes
        $fechaDesde = $params['fecha_desde'] ?? date("Y-m-d", mktime(0, 0, 0, $month, 1, $year));
        $fechaHasta = $params['fecha_hasta'] ?? date("Y-m-t", mktime(0, 0, 0, $month, 1, $year));
        
        // Obtener liquidaciones si no se proporcionan
        $liquidaciones = $params['liquidaciones'] ?? null;
        if (!$liquidaciones) {
            $liquidaciones = $this->obtenerLiquidaciones($year, $month);
            if (empty($liquidaciones)) {
                return [
                    'status' => 'warning',
                    'message' => "No se encontraron liquidaciones para el período {$year}-{$month}",
                    'data' => null
                ];
            }
        }
        
        // Usar la primera liquidación (o la especificada)
        $nroLiqui = $params['nro_liqui'] ?? $liquidaciones[0];
        $codnConce = $params['codn_conce'] ?? 830;
        $nroVarLicencia = $params['nrovar_licencia'] ?? 7;
        
        Log::info('Iniciando actualización de embarazadas en SICOSS', [
            'fecha_desde' => $fechaDesde,
            'fecha_hasta' => $fechaHasta,
            'nro_liqui' => $nroLiqui,
            'codn_conce' => $codnConce,
            'nrovar_licencia' => $nroVarLicencia
        ]);
        
        try {
            // Variable para capturar el ID del log
            $logId = null;
            
            // Llamar al procedimiento almacenado
            DB::connection($this->getConnectionName())->statement(
                "CALL suc.sicoss_mapuche_update_embarazadas(?, ?, ?, ?, ?, ?)",
                [$fechaDesde, $fechaHasta, $nroLiqui, $codnConce, $nroVarLicencia, &$logId]
            );
            
            // Obtener detalles del log si está disponible
            $logDetails = null;
            if ($logId) {
                $logDetails = DB::connection($this->getConnectionName())
                    ->table('suc.log_operaciones')
                    ->where('id', $logId)
                    ->first();
            }
            
            // Si no tenemos detalles del log, intentar obtener el último registro
            if (!$logDetails) {
                $logDetails = DB::connection($this->getConnectionName())
                    ->table('suc.log_operaciones')
                    ->where('operacion', 'sicoss_mapuche_update_embarazadas')
                    ->orderBy('id', 'desc')
                    ->first();
            }
            
            $registrosAfectados = $logDetails->registros_afectados ?? 0;
            $duracionMs = $logDetails->duracion_ms ?? 0;
            
            Log::info('Actualización de embarazadas completada', [
                'registros_afectados' => $registrosAfectados,
                'duracion_ms' => $duracionMs
            ]);
            
            return [
                'status' => 'success',
                'message' => "Actualización completada: {$registrosAfectados} registros actualizados",
                'data' => [
                    'registros_afectados' => $registrosAfectados,
                    'duracion_ms' => $duracionMs,
                    'log_id' => $logId,
                    'log_details' => $logDetails
                ]
            ];
        } catch (\Exception $e) {
            Log::error('Error en actualización de embarazadas', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return [
                'status' => 'error',
                'message' => 'Error: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }
    
    /**
     * Obtiene las liquidaciones disponibles para un período fiscal
     *
     * @param int $year Año
     * @param int $month Mes
     * @return array Números de liquidación
     */
    protected function obtenerLiquidaciones(int $year, int $month): array
    {
        // Usar el modelo Dh22 para obtener liquidaciones que generan impositivo
        try {
            return \App\Models\Mapuche\Dh22::FilterByYearMonth($year, $month)
                ->generaImpositivo()
                ->pluck('nro_liqui')
                ->toArray();
        } catch (\Exception $e) {
            Log::error('Error al obtener liquidaciones', [
                'error' => $e->getMessage(),
                'year' => $year,
                'month' => $month
            ]);
            return [];
        }
    }
}
