<?php

namespace App\Repositories\Sicoss;

use App\Contracts\DatabaseOperationInterface;
use App\Contracts\Dh21RepositoryInterface;
use App\Data\Sicoss\SicossProcessData;
use App\Repositories\Sicoss\Contracts\SicossLegajoFilterRepositoryInterface;
use App\Repositories\Sicoss\Contracts\SicossLegajoProcessorRepositoryInterface;
use App\Repositories\Sicoss\Contracts\SicossOrchestatorRepositoryInterface;
use App\Traits\MapucheConnectionTrait;
use Illuminate\Support\Facades\Log;

class SicossOrchestatorRepository implements SicossOrchestatorRepositoryInterface
{
    use MapucheConnectionTrait;

    protected string $codc_reparto;

    protected array $archivos = [];

    public function __construct(
        protected SicossLegajoFilterRepositoryInterface $sicossLegajoFilterRepository,
        protected SicossLegajoProcessorRepositoryInterface $sicossLegajoProcessorRepository,
        protected Dh21RepositoryInterface $dh21Repository,
        protected DatabaseOperationInterface $databaseOperation,
    ) {
    }

    /**
     * Ejecuta el proceso completo de generación SICOSS
     * Orquesta todo el flujo principal según configuración.
     *
     * @param SicossProcessData $datos Datos de configuración del proceso
     * @param array $periodo_fiscal Período fiscal con formato ['mes' => int, 'ano' => int]
     * @param array $filtros Filtros de procesamiento con formato ['opcion_retro' => int, 'where' => string, 'where_periodo' => string]
     * @param string $path Ruta donde se guardarán los archivos generados
     * @param array $licencias_agentes Lista de agentes con licencias
     * @param bool $retornar_datos Indica si se deben retornar los datos procesados
     *
     * @throws \Exception Si ocurre un error durante el proceso
     *
     * @return array Resultado del proceso según el flujo ejecutado
     */
    public function ejecutarProcesoCompleto(
        SicossProcessData $datos,
        array $periodo_fiscal,
        array $filtros,
        string $path,
        array $licencias_agentes,
        bool $retornar_datos,
    ): array {
        try {
            $per_mesct = $periodo_fiscal['mes'];
            $per_anoct = $periodo_fiscal['ano'];
            $opcion_retro = $filtros['opcion_retro'];
            $where = $filtros['where'];
            $where_periodo = $filtros['where_periodo'];

            // Ejecutar flujo según configuración de retro
            if ($opcion_retro == 0) {
                return $this->procesarSinRetro(
                    $datos,
                    $per_anoct,
                    $per_mesct,
                    $where_periodo,
                    $where,
                    $path,
                    $licencias_agentes,
                    $retornar_datos,
                );
            }
            return $this->procesarConRetro(
                $datos,
                $per_anoct,
                $per_mesct,
                $where,
                $path,
                $licencias_agentes,
                $retornar_datos,
            );


        } catch (\Exception $e) {
            Log::error('Error en orquestación de proceso SICOSS', [
                'error' => $e->getMessage(),
                'datos' => $datos,
            ]);
            throw $e;
        }
    }

    /**
     * Procesa SICOSS sin períodos retro
     * Flujo simplificado para período vigente únicamente.
     *
     * @param SicossProcessData $datos Datos de configuración del proceso
     * @param int $per_anoct Año del período
     * @param int $per_mesct Mes del período
     * @param string $where_periodo Condición WHERE del período
     * @param string $where Condición WHERE base
     * @param string $path Ruta de archivos
     * @param array $licencias_agentes Licencias de agentes
     * @param bool $retornar_datos Indica si se deben retornar los datos procesados
     *
     * @throws \Exception Si ocurre un error durante el proceso
     *
     * @return array Resultado del proceso según el flujo ejecutado
     */
    public function procesarSinRetro(
        SicossProcessData $datos,
        int $per_anoct,
        int $per_mesct,
        string $where_periodo,
        string $where,
        string $path,
        array $licencias_agentes,
        bool $retornar_datos,
    ) {
        try {
            $nombre_arch = 'sicoss';
            $periodo = 'Vigente_sin_retro';
            $this->archivos[$periodo] = $path . $nombre_arch;

            $legajos = $this->sicossLegajoFilterRepository->obtenerLegajos(
                $this->codc_reparto,
                $where_periodo,
                $where,
                $datos->check_lic,
                $datos->check_sin_activo,
            );

            $periodo_display = $per_mesct . '/' . $per_anoct . ' (Vigente)';

            if ($retornar_datos === true) {
                return $this->sicossLegajoProcessorRepository->procesarSicoss(
                    $datos,
                    $per_anoct,
                    $per_mesct,
                    $legajos,
                    $nombre_arch,
                    $licencias_agentes,
                    $datos->check_retro,
                    $datos->check_sin_activo,
                    $retornar_datos,
                );
            }

            $totales[$periodo_display] = $this->sicossLegajoProcessorRepository->procesarSicoss(
                $datos,
                $per_anoct,
                $per_mesct,
                $legajos,
                $nombre_arch,
                $licencias_agentes,
                $datos->check_retro,
                $datos->check_sin_activo,
                $retornar_datos,
            );

            // Limpiar tabla temporal usando la nueva abstracción
            $this->databaseOperation->dropTemporaryTable('conceptos_liquidados');

            Log::info('Procesamiento sin retro completado', [
                'archivo' => $nombre_arch,
                'legajos_procesados' => \count($legajos),
            ]);

            return $totales;

        } catch (\Exception $e) {
            Log::error('Error en procesamiento sin retro', [
                'error' => $e->getMessage(),
                'periodo' => "{$per_mesct}/{$per_anoct}",
            ]);
            throw $e;
        }
    }

    /**
     * Procesa SICOSS con períodos retro
     * Flujo complejo que incluye períodos históricos.
     *
     * @param SicossProcessData $datos Datos de configuración del proceso
     * @param int $per_anoct Año del período
     * @param int $per_mesct Mes del período
     * @param string $where Condición WHERE base
     * @param string $path Ruta de archivos
     * @param array $licencias_agentes Licencias de agentes
     * @param bool $retornar_datos Indica si se deben retornar los datos procesados
     *
     * @throws \Exception Si ocurre un error durante el proceso
     *
     * @return array Resultado del proceso según el flujo ejecutado
     */
    public function procesarConRetro(
        SicossProcessData $datos,
        int $per_anoct,
        int $per_mesct,
        string $where,
        string $path,
        array $licencias_agentes,
        bool $retornar_datos,
    ): array {
        try {
            $totales = [];

            // Obtener períodos retroactivos
            $periodos_retro = $this->dh21Repository->obtenerPeriodosRetro(
                $datos->check_lic,
                $datos->check_retro,
            );

            Log::info('Iniciando procesamiento con retro', [
                'periodos_retro' => \count($periodos_retro),
                'periodo_actual' => "{$per_mesct}/{$per_anoct}",
            ]);

            // Obtener legajos una sola vez para todos los períodos
            $legajos = $this->sicossLegajoFilterRepository->obtenerLegajos(
                $this->codc_reparto,
                ' true ',
                $where,
                $datos->check_lic,
                $datos->check_sin_activo,
            );

            // Procesar período vigente
            $totales_vigente = $this->procesarPeriodoVigente(
                $datos,
                $per_anoct,
                $per_mesct,
                $legajos,
                $path,
                $licencias_agentes,
                $retornar_datos,
            );
            $totales = array_merge($totales, $totales_vigente);

            // Procesar cada período retroactivo
            foreach ($periodos_retro as $periodo_data) {
                $totales_retro = $this->procesarPeriodoRetro(
                    $periodo_data,
                    $datos,
                    $per_anoct,
                    $per_mesct,
                    $where,
                    $path,
                    $licencias_agentes,
                    $retornar_datos,
                );
                $totales = array_merge($totales, $totales_retro);
            }

            // Limpiar tabla temporal
            $this->databaseOperation->dropTemporaryTable('conceptos_liquidados');

            Log::info('Procesamiento con retro completado', [
                'total_periodos' => \count($totales),
                'legajos_base' => \count($legajos),
            ]);

            return $totales;

        } catch (\Exception $e) {
            Log::error('Error en procesamiento con retro', [
                'error' => $e->getMessage(),
                'periodo' => "{$per_mesct}/{$per_anoct}",
            ]);
            throw $e;
        }
    }

    public function procesarResultadoFinal(array $totales, string $testeo_directorio_salida = '', string $testeo_prefijo_archivos = '')
    {
        try {
            // Si se especifica directorio de testeo, mover archivos
            if (!empty($testeo_directorio_salida)) {
                $this->moverArchivosTesteo($testeo_directorio_salida, $testeo_prefijo_archivos);
            }

            Log::info('Proceso SICOSS finalizado exitosamente', [
                'total_periodos' => \count($totales),
                'archivos_generados' => \count($this->archivos),
            ]);

            return [
                'totales' => $totales,
                'archivos' => $this->archivos,
                'status' => 'completed',
            ];

        } catch (\Exception $e) {
            Log::error('Error en procesamiento de resultado final', [
                'error' => $e->getMessage(),
                'totales' => \count($totales),
            ]);
            throw $e;
        }
    }

    public function setCodigoReparto(string $codc_reparto): void
    {
        $this->codc_reparto = $codc_reparto;
    }

    public function getArchivosGenerados(): array
    {
        return $this->archivos;
    }

    protected function procesarPeriodoRetro(array $periodo_data, SicossProcessData $datos, int $per_anoct, int $per_mesct, string $where, string $path, array $licencias_agentes, bool $retornar_datos): array
    {
        try {
            $nombre_arch = 'sicoss_retro_' . $periodo_data['ano_retro'] . '_' . $periodo_data['mes_retro'];
            $periodo = $periodo_data['ano_retro'] . $periodo_data['mes_retro'];
            $item = $periodo_data['mes_retro'] . '/' . $periodo_data['ano_retro'];

            $this->archivos[$periodo] = $path . $nombre_arch;

            // Obtener conceptos liquidados para el período específico
            $where_periodo_retro = ' ano_retro = ' . $periodo_data['ano_retro'] .
                                 ' AND mes_retro = ' . $periodo_data['mes_retro'];

            $this->dh21Repository->obtenerConceptosLiquidadosSicoss(
                $periodo_data['ano_retro'],
                $periodo_data['mes_retro'],
                $where,
            );

            // Obtener legajos para este período específico
            $legajos = $this->sicossLegajoFilterRepository->obtenerLegajos(
                $this->codc_reparto,
                $where_periodo_retro,
                $where,
                $datos->check_lic,
                $datos->check_sin_activo,
            );

            Log::info('Procesando período retro', [
                'periodo' => $item,
                'archivo' => $nombre_arch,
                'legajos' => \count($legajos),
            ]);

            $subtotal = $this->sicossLegajoProcessorRepository->procesarSicoss(
                $datos,
                $per_anoct,
                $per_mesct,
                $legajos,
                $nombre_arch,
                null,
                $datos->check_retro,
                $datos->check_sin_activo,
                $retornar_datos,
            );

            return [$item => $subtotal];

        } catch (\Exception $e) {
            Log::error('Error en procesamiento de período retro', [
                'error' => $e->getMessage(),
                'periodo_retro' => $periodo_data,
            ]);
            throw $e;
        }
    }

    protected function procesarPeriodoVigente(SicossProcessData $datos, int $per_anoct, int $per_mesct, array $legajos, string $path, array $licencias_agentes, bool $retornar_datos): array
    {
        try {
            $nombre_arch = 'sicoss';
            $periodo = 'Vigente';
            $this->archivos[$periodo] = $path . $nombre_arch;

            $periodo_display = $per_mesct . '/' . $per_anoct . ' (Vigente)';

            Log::info('Procesando período vigente', [
                'periodo' => $periodo_display,
                'archivo' => $nombre_arch,
                'legajos' => \count($legajos),
            ]);

            $subtotal = $this->sicossLegajoProcessorRepository->procesarSicoss(
                $datos,
                $per_anoct,
                $per_mesct,
                $legajos,
                $nombre_arch,
                $licencias_agentes,
                $datos->check_retro,
                $datos->check_sin_activo,
                $retornar_datos,
            );

            return [$periodo_display => $subtotal];

        } catch (\Exception $e) {
            Log::error('Error en procesamiento de período vigente', [
                'error' => $e->getMessage(),
                'periodo' => "{$per_mesct}/{$per_anoct}",
            ]);
            throw $e;
        }
    }

    /**
     * Mueve archivos al directorio de testeo si se especifica.
     */
    protected function moverArchivosTesteo(string $directorio_testeo, string $prefijo = ''): void
    {
        try {
            // Crear directorio de testeo si no existe
            if (!is_dir($directorio_testeo)) {
                mkdir($directorio_testeo, 0o755, true);
            }

            foreach ($this->archivos as $periodo => $archivo_origen) {
                if (file_exists($archivo_origen . '.txt')) {
                    $nombre_archivo = basename($archivo_origen);
                    $archivo_destino = $directorio_testeo . '/' . $prefijo . $nombre_archivo . '.txt';

                    copy($archivo_origen . '.txt', $archivo_destino);

                    Log::debug('Archivo movido para testeo', [
                        'origen' => $archivo_origen . '.txt',
                        'destino' => $archivo_destino,
                    ]);
                }
            }

        } catch (\Exception $e) {
            Log::warning('Error al mover archivos de testeo', [
                'error' => $e->getMessage(),
                'directorio' => $directorio_testeo,
            ]);
            // No lanzar excepción, solo advertir
        }
    }
}
