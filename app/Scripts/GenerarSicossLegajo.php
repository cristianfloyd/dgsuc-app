<?php

namespace App\Scripts;

use App\Services\Afip\Sicoss;
use Illuminate\Support\Facades\DB;

class GenerarSicossLegajo
{
    public function generar($numeroLegajo)
    {
        try {
            // Configuración de datos para el SICOSS
            $datos = [
                'nro_legaj' => $numeroLegajo,
                'check_retro' => 0, // 0 para periodo vigente, 1 para incluir retroactivos
                'check_lic' => false, // Incluir licencias
                'check_sin_activo' => false, // No incluir agentes sin cargos activos
                'truncaTope' => true, // Usar topes jubilatorios por defecto, excepto si se especifica lo contrario
                'TopeJubilatorioPatronal' => null,
                'TopeJubilatorioPersonal' => null,
                'TopeOtrosAportesPersonal' => null,
                'nro_liqui' => 10, // Filtrar por liquidación específica si se especifica
            ];

            // Generar el SICOSS
            $resultado = Sicoss::genera_sicoss(
                $datos,
                '', // directorio de salida para testing
                '', // prefijo de archivos para testing
                true // retornar datos
            );

            // El archivo se generará en storage/comunicacion/sicoss/
            // y se creará un archivo ZIP con todos los archivos generados

            return [
                'success' => true,
                'message' => 'SICOSS generado exitosamente',
                'archivo' => storage_path('comunicacion/sicoss/sicoss.txt'),
                'zip' => storage_path('comunicacion/sicoss/sicoss.zip'),
                'resultado' => $resultado
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al generar SICOSS: ' . $e->getMessage()
            ];
        }
    }
}