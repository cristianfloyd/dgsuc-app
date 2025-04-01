<?php

namespace App\Services\Sicoss;

use Exception;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\ControlCuilsDiferencia;
use App\Exports\Sicoss\ConceptosExport;
use App\Models\ControlConceptosPeriodo;
use App\Models\ControlAportesDiferencia;
use App\Exports\Sicoss\CuilsDiferenciasExport;
use App\Models\ControlContribucionesDiferencia;
use App\Exports\Sicoss\AportesDiferenciasExport;
use App\Exports\Sicoss\ContribucionesDiferenciasExport;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Servicio especializado en la exportación de informes y reportes SICOSS
 */
class SicossReportExportService
{
    /**
     * Exporta los datos según la pestaña activa
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
            $data = $this->prepareExportData($activeTab, $year, $month);
            
            if (!$data) {
                throw new Exception('No hay datos para exportar en esta pestaña.');
            }
            
            // Construir el nombre del archivo con la extensión explícita
            $filename = sprintf(
                '%s-%d-%02d-%s.xlsx',
                $data['filename'],
                $year,
                $month,
                now()->format('Ymd-His')
            );
            
            Log::info("Exportando tabla {$activeTab} para período {$year}-{$month} a archivo {$filename}");
            
            // Crear la instancia del exportador con los parámetros adicionales
            $exporter = new ($data['class'])(
                $data['data'],
                $year,
                $month
            );
            
            // Especificar el tipo de escritor explícitamente
            return Excel::download(
                $exporter,
                $filename,
                \Maatwebsite\Excel\Excel::XLSX
            );
        } catch (Exception $e) {
            Log::error("Error exportando tabla {$activeTab}: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Prepara los datos para la exportación según la pestaña activa
     *
     * @param string $activeTab Pestaña activa
     * @param int $year Año del período
     * @param int $month Mes del período
     * @return array|null Datos preparados para la exportación o null si no hay datos
     */
    private function prepareExportData(string $activeTab, int $year, int $month): ?array
    {
        return match ($activeTab) {
            'diferencias_aportes' => [
                'class' => AportesDiferenciasExport::class,
                'filename' => 'diferencias-aportes',
                'title' => 'Diferencias por Aportes',
                'data' => ControlAportesDiferencia::query()
                    ->with(['sicossCalculo', 'relacionActiva', 'dh01'])
                    ->get()
            ],
            'diferencias_contribuciones' => [
                'class' => ContribucionesDiferenciasExport::class,
                'filename' => 'diferencias-contribuciones',
                'title' => 'Diferencias por Contribuciones',
                'data' => ControlContribucionesDiferencia::query()
                    ->with(['sicossCalculo', 'relacionActiva', 'dh01'])
                    ->get()
            ],
            'diferencias_cuils' => [
                'class' => CuilsDiferenciasExport::class,
                'filename' => 'diferencias-cuils',
                'title' => 'CUILs no encontrados',
                'data' => ControlCuilsDiferencia::query()->get()
            ],
            'conceptos' => [
                'class' => ConceptosExport::class,
                'filename' => 'conceptos',
                'title' => 'Conceptos por Período',
                'data' => ControlConceptosPeriodo::query()
                    ->where('year', $year)
                    ->where('month', $month)
                    ->get()
            ],
            default => null
        };
    }
}
