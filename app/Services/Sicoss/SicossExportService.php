<?php

namespace App\Services\Sicoss;

use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Servicio principal para exportaciones de SICOSS
 * Actúa como fachada para los diferentes tipos de exportación
 */
class SicossExportService
{
    /**
     * Constructor con inyección de dependencias
     */
    public function __construct(
        private readonly SicossTxtExportService $txtExportService,
        private readonly SicossExcelExportService $excelExportService,
        private readonly SicossReportExportService $reportExportService
    ) {}

    /**
     * Método genérico para generar un archivo en el formato especificado
     * 
     * @param Collection $registros Registros a incluir en el archivo
     * @param string $formato Formato del archivo ('txt' o 'excel')
     * @param string|null $periodoFiscal Periodo fiscal opcional (se extraerá del primer registro si es null)
     * @return string Ruta completa del archivo generado
     * @throws Exception Si el formato no es soportado o no hay registros
     */
    public function generarArchivo(Collection $registros, string $formato = 'txt', ?string $periodoFiscal = null): string
    {
        try {
            if ($registros->isEmpty()) {
                throw new Exception("No hay registros para exportar");
            }
            
            // Extraer el período fiscal del primer registro si no se proporciona
            if ($periodoFiscal === null) {
                $periodoFiscal = $this->extraerPeriodoFiscal($registros->first());
            }
            
            Log::info("Iniciando exportación de archivo SICOSS en formato {$formato} para período {$periodoFiscal}");
            
            $resultado = match(strtolower($formato)) {
                'excel', 'xlsx' => $this->excelExportService->generarArchivo($registros, $periodoFiscal),
                'txt', 'text' => $this->txtExportService->generarArchivo($registros, $periodoFiscal),
                default => throw new Exception("Formato de exportación no soportado: {$formato}")
            };
            
            Log::info("Exportación SICOSS completada en formato {$formato}");
            return $resultado;
        } catch (Exception $e) {
            Log::error("Error en exportación SICOSS: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Extrae el período fiscal del registro proporcionado
     * 
     * @param mixed $registro Registro del que extraer el período fiscal
     * @return string Período fiscal en formato YYYYMM
     */
    private function extraerPeriodoFiscal($registro): string
    {
        // Si el registro tiene propiedades year y month, usarlas
        if (isset($registro->year) && isset($registro->month)) {
            return $registro->year . str_pad($registro->month, 2, '0', STR_PAD_LEFT);
        }
        
        // Si el registro tiene propiedad periodo_fiscal, usarla
        if (isset($registro->periodo_fiscal)) {
            return $registro->periodo_fiscal;
        }
        
        // Si el registro tiene propiedad periodo, usarla
        if (isset($registro->periodo)) {
            return $registro->periodo;
        }
        
        // Si no se puede extraer, usar el período actual
        return date('Ym');
    }

    /**
     * Exporta los datos de informes según la pestaña activa
     * 
     * @param string $activeTab Pestaña activa que determina qué datos exportar
     * @param int $year Año del período
     * @param int $month Mes del período
     * @return BinaryFileResponse Respuesta con el archivo para descarga
     * @throws Exception Si no hay datos para exportar
     */
    public function exportActiveTable(string $activeTab, int $year, int $month): BinaryFileResponse
    {
        try {
            Log::info("Exportando tabla activa: {$activeTab} para período {$year}-{$month}");
            return $this->reportExportService->exportActiveTable($activeTab, $year, $month);
        } catch (Exception $e) {
            Log::error("Error exportando tabla activa: " . $e->getMessage());
            throw $e;
        }
    }
}
