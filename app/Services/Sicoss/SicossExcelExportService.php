<?php

namespace App\Services\Sicoss;

use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use App\Services\Sicoss\Contracts\SicossExportInterface;

/**
 * Servicio especializado en la exportación de archivos Excel para SICOSS
 */
class SicossExcelExportService implements SicossExportInterface
{
    /**
     * Genera un archivo Excel con los datos de SICOSS
     *
     * @param Collection $registros Registros a incluir en el archivo
     * @param string|null $periodoFiscal Periodo fiscal para el nombre del archivo (formato YYYYMM)
     * @return string Ruta completa del archivo generado
     * @throws Exception Si ocurre un error durante la generación
     */
    public function generarArchivo(Collection $registros, ?string $periodoFiscal = null): string
    {
        try {
            // Usar el periodo fiscal proporcionado o el actual
            $periodoFiscal = $periodoFiscal ?? date('Ym');
            $nombreArchivo = "SICOSS_$periodoFiscal.xlsx";
            $rutaRelativa = "tmp/$nombreArchivo";
            $rutaArchivo = storage_path("app/$rutaRelativa");

            Log::info("Iniciando exportación de archivo SICOSS Excel con " . $registros->count() . " registros");

            // Implementación usando vista para la exportación
            Excel::store(
                view('exports.sicoss', ['registros' => $registros]),
                $rutaRelativa,
                'local'
            );

            Log::info("Exportación SICOSS Excel completada: archivo guardado en {$nombreArchivo}");
            
            return $rutaArchivo;
        } catch (Exception $e) {
            Log::error("Error generando archivo Excel SICOSS: " . $e->getMessage());
            throw new Exception("Error al generar archivo Excel SICOSS: " . $e->getMessage(), 0, $e);
        }
    }
}
