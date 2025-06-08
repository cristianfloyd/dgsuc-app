<?php

namespace App\Scripts;

use App\Services\Afip\Sicoss;
use Illuminate\Support\Facades\Log;
use App\Contracts\SicossGeneratorInterface;
use App\Exceptions\SicossGenerationException;

/**
 * Clase responsable de generar archivos SICOSS para AFIP
 * Permite generar reportes con o sin restricción de legajo específico
 */
class GenerarSicossLegajo implements SicossGeneratorInterface
{
    /**
     * Configuración por defecto para la generación de SICOSS
     */
    private const DEFAULT_CONFIG = [
        'check_retro' => 0,
        'check_lic' => false,
        'check_sin_activo' => false,
        'truncaTope' => true,
        'TopeJubilatorioPatronal' => null,
        'TopeJubilatorioPersonal' => null,
        'TopeOtrosAportesPersonal' => null,
        // 'nro_liqui' => 31,
    ];

    /**
     * Rutas de archivos de salida
     */
    private const OUTPUT_PATHS = [
        'directory' => 'comunicacion/sicoss',
        'file' => 'sicoss.txt',
        'zip' => 'sicoss.zip',
    ];



    /**
     * Genera archivo SICOSS con parámetros opcionales
     * 
     * @param int|null $numeroLegajo Número de legajo específico (opcional)
     * @param array $configuracionPersonalizada Configuración adicional (opcional)
     * @return array Resultado de la operación
     */
    public function generar(?int $numeroLegajo = null, array $configuracionPersonalizada = []): array
    {
        try {
            // Preparar configuración de datos para SICOSS
            $datosConfiguracion = $this->prepararConfiguracion($numeroLegajo, $configuracionPersonalizada);
            
            // Validar configuración antes de procesar
            $this->validarConfiguracion($datosConfiguracion);
            
            // Ejecutar generación del archivo SICOSS
            $resultadoGeneracion = $this->ejecutarGeneracionSicoss($datosConfiguracion);
            
            // Procesar y formatear resultado para mejor legibilidad
            $datosFormateados = $this->formatearResultado($resultadoGeneracion, $numeroLegajo);
            
            // Verificar que los archivos se generaron correctamente
            //$this->verificarArchivosGenerados();
            
            // Registrar operación exitosa
            $this->registrarOperacionExitosa($numeroLegajo);
            
            return $this->construirRespuestaExitosa($datosFormateados);
            
        } catch (SicossGenerationException $e) {
            // Manejo específico de errores de generación SICOSS
            Log::error('Error específico en generación SICOSS', [
                'legajo' => $numeroLegajo,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return $this->construirRespuestaError($e->getMessage());
            
        } catch (\Exception $e) {
            // Manejo general de errores inesperados
            Log::error('Error inesperado en generación SICOSS', [
                'legajo' => $numeroLegajo,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return $this->construirRespuestaError('Error interno del sistema: ' . $e->getMessage());
        }
    }

    /**
     * Prepara la configuración combinando valores por defecto con parámetros específicos
     * 
     * @param int|null $numeroLegajo
     * @param array $configuracionPersonalizada
     * @return array
     */
    private function prepararConfiguracion(?int $numeroLegajo, array $configuracionPersonalizada): array
    {
        // Iniciar con configuración base
        $configuracion = self::DEFAULT_CONFIG;
        
        // Agregar número de legajo solo si se proporciona
        if ($numeroLegajo !== null) {
            $configuracion['nro_legaj'] = $numeroLegajo;
        }
        
        // Fusionar con configuración personalizada (sobrescribe valores por defecto)
        return array_merge($configuracion, $configuracionPersonalizada);
    }

    /**
     * Formatea el resultado para mejor legibilidad
     * 
     * @param mixed $resultado
     * @param int|null $numeroLegajo
     * @return array
     */
    private function formatearResultado($resultado, ?int $numeroLegajo): array
    {
        // Si no hay resultado o está vacío
        if (empty($resultado) || !is_array($resultado)) {
            return [
                'tipo' => 'sin_datos',
                'mensaje' => 'No se encontraron datos para procesar',
                'datos_raw' => $resultado
            ];
        }

        // Si es un legajo específico, formatear datos individuales
        if ($numeroLegajo !== null && isset($resultado[0])) {
            return $this->formatearDatosLegajo($resultado[0]);
        }
        
        // Si son múltiples legajos, formatear resumen
        return $this->formatearResumenMultiple($resultado);
    }

    /**
     * Formatea los datos de un legajo específico
     * 
     * @param array $datosLegajo
     * @return array
     */
    private function formatearDatosLegajo(array $datosLegajo): array
    {
        // Extraer información básica del empleado
        $informacionBasica = [
            'legajo' => $datosLegajo['nro_legaj'] ?? 'N/A',
            'cuit' => $datosLegajo['cuit'] ?? 'N/A',
            'apellido_nombres' => trim($datosLegajo['apyno'] ?? 'N/A'),
            'estado' => $datosLegajo['estado'] ?? 'N/A',
            'dias_trabajados' => $datosLegajo['dias_trabajados'] ?? 0,
        ];

        // Extraer importes relevantes
        $importes = [
            'bruto' => $this->formatearImporte($datosLegajo['IMPORTE_BRUTO'] ?? 0),
            'imponible_1' => $this->formatearImporte($datosLegajo['importeimponible_9'] ?? 0),
            'imponible_2' => $this->formatearImporte($datosLegajo['ImporteImponible_4'] ?? 0),
            'imponible_3' => $this->formatearImporte($datosLegajo['ImporteImponible_6'] ?? 0),
            'imponible_4' => $this->formatearImporte($datosLegajo['ImporteImponible_5'] ?? 0),
            'imponible_5' => $this->formatearImporte($datosLegajo['ImporteImponiblePatronal'] ?? 0),
            'imponible_6' => $this->formatearImporte($datosLegajo['ImporteImponibleSinSAC'] ?? 0),
            'imponible_7' => $this->formatearImporte($datosLegajo['IMPORTE_IMPON'] ?? 0),
            'imponible_8' => $this->formatearImporte($datosLegajo['ImporteImponibleBecario'] ?? 0),
            'imponible_9' => $this->formatearImporte($datosLegajo['Remuner78805'] ?? 0),
        ];

        // Información adicional relevante
        $informacionAdicional = [
            'codigo_actividad' => $datosLegajo['codigoactividad'] ?? 'N/A',
            'codigo_contratacion' => $datosLegajo['codigocontratacion'] ?? 'N/A',
            'regimen' => $datosLegajo['regimen'] ?? 'N/A',
            'obra_social' => $datosLegajo['codigo_os'] ?? 'N/A',
        ];

        return [
            'tipo' => 'legajo_individual',
            'informacion_basica' => $informacionBasica,
            'importes' => $importes,
            'informacion_adicional' => $informacionAdicional,
            'datos_completos' => $datosLegajo // Para debugging si es necesario
        ];
    }

    /**
     * Formatea resumen para múltiples legajos
     * 
     * @param array $resultados
     * @return array
     */
    private function formatearResumenMultiple(array $resultados): array
    {
        
        $totalLegajos = count($resultados);
        $totalBruto = 0;
        $totalImponible = 0;
        $legajosProcesados = [];

        foreach ($resultados as $legajo) {
            $totalBruto += floatval($legajo['IMPORTE_BRUTO'] ?? 0);
            $totalImponible += floatval($legajo['importeimponible_9'] ?? 0);
            
            $legajosProcesados[] = [
                'legajo' => $legajo['nro_legaj'] ?? 'N/A',
                'apellido_nombres' => trim($legajo['apyno'] ?? 'N/A'),
                'bruto' => $this->formatearImporte($legajo['IMPORTE_BRUTO'] ?? 0),
                'imponible_principal' => $this->formatearImporte($legajo['importeimponible_9'] ?? 0),
            ];
        }

        return [
            'tipo' => 'multiples_legajos',
            'resumen' => [
                'total_legajos' => $totalLegajos,
                'total_bruto' => $this->formatearImporte($totalBruto),
                'total_imponible' => $this->formatearImporte($totalImponible),
            ],
            'legajos' => $legajosProcesados,
            'datos_completos' => $resultados // Para debugging si es necesario
        ];
    }

    /**
     * Formatea un importe para mostrar con separadores de miles
     * 
     * @param float|int|string $importe
     * @return string
     */
    private function formatearImporte($importe): string
    {
        $valor = floatval($importe);
        
        if ($valor == 0) {
            return '0,00';
        }
        
        return number_format($valor, 2, ',', '.');
    }

    /**
     * Valida que la configuración tenga los parámetros mínimos requeridos
     * 
     * @param array $configuracion
     * @throws SicossGenerationException
     */
    private function validarConfiguracion(array $configuracion): void
    {
        // Validar que nro_liqui esté presente y sea válido
        // if (!isset($configuracion['nro_liqui']) || !is_numeric($configuracion['nro_liqui'])) {
        //     throw new SicossGenerationException('Número de liquidación inválido o faltante');
        // }
        
        // Validar legajo si está presente
        if (isset($configuracion['nro_legaj']) && !is_numeric($configuracion['nro_legaj'])) {
            throw new SicossGenerationException('Número de legajo debe ser numérico');
        }
    }

    /**
     * Ejecuta la generación del archivo SICOSS utilizando el servicio AFIP
     * 
     * @param array $configuracion
     * @return mixed
     * @throws SicossGenerationException
     */
    private function ejecutarGeneracionSicoss(array $configuracion)
    {
        try {
            // Llamar al servicio SICOSS con parámetros optimizados
            return Sicoss::genera_sicoss(
                $configuracion,
                '', // directorio de salida para testing
                '', // prefijo de archivos para testing
                false // retornar datos para verificación
            );
            
        } catch (\Exception $e) {
            throw new SicossGenerationException(
                'Fallo en la generación del archivo SICOSS: ' . $e->getMessage(),
                0,
                $e
            );
        }
    }

    /**
     * Verifica que los archivos esperados se hayan generado correctamente
     * 
     * @throws SicossGenerationException
     */
    private function verificarArchivosGenerados(): void
    {
        $rutaArchivo = $this->obtenerRutaCompleta(self::OUTPUT_PATHS['file']);
        
        // Verificar existencia del archivo principal
        if (!file_exists($rutaArchivo)) {
            throw new SicossGenerationException('El archivo SICOSS no se generó correctamente');
        }
        
        // Verificar que el archivo no esté vacío
        if (filesize($rutaArchivo) === 0) {
            throw new SicossGenerationException('El archivo SICOSS generado está vacío');
        }
    }

    /**
     * Registra la operación exitosa en los logs del sistema
     * 
     * @param int|null $numeroLegajo
     */
    private function registrarOperacionExitosa(?int $numeroLegajo): void
    {
        $mensaje = $numeroLegajo 
            ? "SICOSS generado exitosamente para legajo: {$numeroLegajo}"
            : "SICOSS generado exitosamente para todos los legajos";
            
        Log::info($mensaje, [
            'legajo' => $numeroLegajo,
            'timestamp' => now(),
            'usuario' => auth()->guard('web')->id() ?? 'sistema'
        ]);
    }

    /**
     * Construye respuesta exitosa estandarizada
     * 
     * @param array $datosFormateados
     * @return array
     */
    private function construirRespuestaExitosa(array $datosFormateados): array
    {
        return [
            'success' => true,
            'message' => 'SICOSS generado exitosamente',
            'data' => [
                'archivo' => $this->obtenerRutaCompleta(self::OUTPUT_PATHS['file']),
                'zip' => $this->obtenerRutaCompleta(self::OUTPUT_PATHS['zip']),
                'datos_procesados' => $datosFormateados,
                'timestamp' => now()->toISOString()
            ]
        ];
    }

    /**
     * Construye respuesta de error estandarizada
     * 
     * @param string $mensaje
     * @return array
     */
    private function construirRespuestaError(string $mensaje): array
    {
        return [
            'success' => false,
            'message' => $mensaje,
            'data' => null,
            'timestamp' => now()->toISOString()
        ];
    }

    /**
     * Obtiene la ruta completa del archivo en el directorio de storage
     * 
     * @param string $archivo
     * @return string
     */
    private function obtenerRutaCompleta(string $archivo): string
    {
        return storage_path(self::OUTPUT_PATHS['directory'] . '/' . $archivo);
    }
}
