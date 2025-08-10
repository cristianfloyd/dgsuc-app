<?php

namespace App\Services\Reportes;

use App\Enums\BloqueosEstadoEnum;
use App\Models\Reportes\BloqueosDataModel;
use Illuminate\Support\Collection;

/**
 * Servicio para validación de bloqueos.
 *
 * Este servicio encapsula toda la lógica relacionada con la validación
 * de registros de bloqueos, tanto individuales como en lote.
 */
class BloqueosValidationService
{
    /**
     * Valida un registro de bloqueo individual.
     *
     * @param BloqueosDataModel $record
     *
     * @return array Resultado de la validación con estado y mensaje
     */
    public function validarRegistro(BloqueosDataModel $record): array
    {
        try {
            // Ejecutamos la validación en el modelo
            $record->validarEstado();

            // Preparamos la respuesta según el estado resultante
            $mensaje = match ($record->estado) {
                BloqueosEstadoEnum::VALIDADO => 'Registro validado correctamente',
                BloqueosEstadoEnum::FALTA_CARGO_ASOCIADO => "Error: {$record->mensaje_error}",
                BloqueosEstadoEnum::FECHA_CARGO_NO_COINCIDE => "Error: {$record->mensaje_error}",
                default => "Error en la validación: {$record->mensaje_error}"
            };

            $color = match ($record->estado) {
                BloqueosEstadoEnum::VALIDADO => 'success',
                BloqueosEstadoEnum::FECHAS_COINCIDENTES => 'warning',
                BloqueosEstadoEnum::LICENCIA_YA_BLOQUEADA => 'warning',
                default => 'danger'
            };

            return [
                'mensaje' => $mensaje,
                'color' => $color,
                'estado' => $record->estado,
                'success' => $record->estado === BloqueosEstadoEnum::VALIDADO,
            ];
        } catch (\Exception $e) {
            // Capturamos cualquier excepción no manejada
            return [
                'mensaje' => 'Error inesperado: ' . $e->getMessage(),
                'color' => 'danger',
                'estado' => null,
                'success' => false,
            ];
        }
    }

    /**
     * Valida múltiples registros de bloqueo.
     *
     * @param Collection $records
     *
     * @return array Estadísticas del proceso de validación
     */
    public function validarMultiplesRegistros(Collection $records): array
    {
        $totalRegistros = $records->count();
        $estadisticas = [
            'total' => $totalRegistros,
            'validados' => 0,
            'faltaCargoAsociado' => 0,
            'fechaCargoNoCoincide' => 0,
            'licenciaYaBloqueada' => 0,
            'fechasCoincidentes' => 0,
            'conError' => 0,
            'errores' => [],
        ];

        try {
            foreach ($records as $record) {
                try {
                    $record->validarEstado();

                    // Actualizamos las estadísticas según el estado
                    if ($record->estado === BloqueosEstadoEnum::VALIDADO) {
                        $estadisticas['validados']++;
                    } elseif ($record->estado === BloqueosEstadoEnum::FALTA_CARGO_ASOCIADO) {
                        $estadisticas['faltaCargoAsociado']++;
                    } elseif ($record->estado === BloqueosEstadoEnum::FECHA_CARGO_NO_COINCIDE) {
                        $estadisticas['fechaCargoNoCoincide']++;
                    } elseif ($record->estado === BloqueosEstadoEnum::LICENCIA_YA_BLOQUEADA) {
                        $estadisticas['licenciaYaBloqueada']++;
                    } elseif ($record->estado === BloqueosEstadoEnum::FECHAS_COINCIDENTES) {
                        $estadisticas['fechasCoincidentes']++;
                    } else {
                        $estadisticas['conError']++;
                        $estadisticas['errores'][] = [
                            'id' => $record->id,
                            'mensaje' => $record->mensaje_error ?? 'Error desconocido',
                        ];
                    }
                } catch (\Exception $e) {
                    $estadisticas['conError']++;
                    $estadisticas['errores'][] = [
                        'id' => $record->id,
                        'mensaje' => $e->getMessage(),
                    ];
                }
            }

            return $estadisticas;
        } catch (\Exception $e) {
            // Capturamos cualquier excepción no manejada en el proceso global
            $estadisticas['error_general'] = $e->getMessage();
            return $estadisticas;
        }
    }

    /**
     * Genera un mensaje de resumen para mostrar al usuario.
     *
     * @param array $estadisticas
     *
     * @return string
     */
    public function generarMensajeResumen(array $estadisticas): string
    {
        return "Total procesados: {$estadisticas['total']}\n" .
            "Validados: {$estadisticas['validados']}\n" .
            "Falta cargo asociado: {$estadisticas['faltaCargoAsociado']}\n" .
            "Fecha cargo no coincide: {$estadisticas['fechaCargoNoCoincide']}\n" .
            "Licencia ya bloqueada: {$estadisticas['licenciaYaBloqueada']}\n" .
            "Fechas coincidentes: {$estadisticas['fechasCoincidentes']}\n" .
            "Con error: {$estadisticas['conError']}";
    }

    /**
     * Valida todos los registros de bloqueos existentes en la base de datos.
     *
     * @return array Estadísticas del proceso de validación
     */
    public function validarTodosLosRegistros(): array
    {
        $registros = BloqueosDataModel::all();
        return $this->validarMultiplesRegistros($registros);
    }
}
