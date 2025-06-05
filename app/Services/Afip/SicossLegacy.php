<?php

namespace App\Services\Afip;

use App\Models\Dh01;
use App\Models\Dh03;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Mapuche\MapucheConfig;
use App\Traits\MapucheConnectionTrait;
use App\Services\Mapuche\LicenciaService;
use App\Contracts\Dh01RepositoryInterface;
use App\Contracts\Dh21RepositoryInterface;
use App\Contracts\DatabaseOperationInterface;
use App\Services\Mapuche\PeriodoFiscalService;
use App\Repositories\Sicoss\LicenciaRepository;
use App\Repositories\Sicoss\Contracts\Dh03RepositoryInterface;
use App\Repositories\Sicoss\Contracts\LicenciaRepositoryInterface;
use App\Repositories\Sicoss\Contracts\SicossEstadoRepositoryInterface;
use App\Repositories\Sicoss\Contracts\SicossCalculoRepositoryInterface;
use App\Repositories\Sicoss\Contracts\SicossFormateadorRepositoryInterface;
use App\Repositories\Sicoss\Contracts\SicossLegajoFilterRepositoryInterface;
use App\Repositories\Sicoss\Contracts\SicossConfigurationRepositoryInterface;
use App\Repositories\Sicoss\Contracts\SicossLegajoProcessorRepositoryInterface;

class SicossLegacy
{
    use MapucheConnectionTrait;

    protected static $codc_reparto;
    protected static $archivos;


    /**
     * Create a new class instance.
     */
    public function __construct(
        protected LicenciaRepositoryInterface $licenciaRepository,
        protected Dh03RepositoryInterface $dh03Repository,
        protected Dh21RepositoryInterface $dh21Repository,
        protected Dh01RepositoryInterface $dh01Repository,
        protected SicossCalculoRepositoryInterface $sicossCalculoRepository,
        protected SicossEstadoRepositoryInterface $sicossEstadoRepository,
        protected SicossFormateadorRepositoryInterface $sicossFormateadorRepository,
        protected SicossConfigurationRepositoryInterface $sicossConfigurationRepository,
        protected SicossLegajoFilterRepositoryInterface $sicossLegajoFilterRepository,
        protected SicossLegajoProcessorRepositoryInterface $sicossLegajoProcessorRepository,
        protected DatabaseOperationInterface $databaseOperation
    ) {}



    public function genera_sicoss($datos, $testeo_directorio_salida = '', $testeo_prefijo_archivos = '', $retornar_datos = FALSE)
    {
        try {
            //code...

            Log::info('Iniciando generación de SICOSS', [
                'datos' => $datos,
                'retornar_datos' => $retornar_datos
            ]);

            // Cargar configuraciones usando el nuevo repositorio
            $this->sicossConfigurationRepository->cargarConfiguraciones();

            // Obtener período fiscal usando el nuevo repositorio
            $periodo_fiscal = $this->sicossConfigurationRepository->obtenerPeriodoFiscal();
            $per_mesct = $periodo_fiscal['mes'];
            $per_anoct = $periodo_fiscal['ano'];

            // Generar filtros básicos usando el nuevo repositorio
            $filtros = $this->sicossConfigurationRepository->generarFiltrosBasicos($datos);
            $opcion_retro   = $filtros['opcion_retro'];
            $filtro_legajo  = $filtros['filtro_legajo'];
            $where          = $filtros['where'];
            $where_periodo  = $filtros['where_periodo'];

            //si se envia nro_liqui desde la generacion de libro de sueldo
            $this->procesarConceptosLiquidados($datos, $per_anoct, $per_mesct, $where);


            // Inicializar configuración de archivos usando el nuevo repositorio
            $config_archivos = $this->sicossConfigurationRepository->inicializarConfiguracionArchivos();
            $path = $config_archivos['path'];
            $totales = $config_archivos['totales'];

            // Obtener licencias de agentes usando el nuevo repositorio
            $licencias_agentes = $this->licenciaRepository->getLicenciasVigentes($where);


            // Si no tengo tildado el check el proceso genera un unico archivo sin tener en cuenta a�o y mes retro
            if ($opcion_retro == 0) {
                $totales = $this->procesarSinRetro(
                    $datos,
                    $per_anoct,
                    $per_mesct,
                    $where_periodo,
                    $where,
                    $path,
                    $licencias_agentes,
                    $retornar_datos
                );
            } else {
                $totales = $this->procesarConRetro(
                    $datos,
                    $per_anoct,
                    $per_mesct,
                    $where,
                    $path,
                    $licencias_agentes,
                    $retornar_datos
                );
            }

            // Limpiar tablas temporales
            $this->limpiarTablasTemporales();

            // Procesar resultado final
            return $this->procesarResultadoFinal(
                $totales,
                $testeo_directorio_salida,
                $testeo_prefijo_archivos
            );
        } catch (\Exception $e) {
            Log::error('Error en generación de SICOSS', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'datos' => $datos
            ]);

            // Limpiar tablas temporales en caso de error
            $this->limpiarTablasTemporales();

            throw $e;
        }
    }

    /**
     * Procesa los conceptos liquidados según los parámetros
     *
     * @param array $datos Datos de configuración
     * @param int $per_anoct Año del período
     * @param int $per_mesct Mes del período
     * @param string $where Condición WHERE base
     * @return void
     */
    protected function procesarConceptosLiquidados(array $datos, int $per_anoct, int $per_mesct, string $where): void
    {
        try {
            // Si se envía nro_liqui desde la generación de libro de sueldo
            if (isset($datos['nro_liqui'])) {
                $where_liqui = $where . ' AND dh21.nro_liqui = ' . $datos['nro_liqui'];
                $this->dh21Repository->obtenerConceptosLiquidadosSicoss($per_anoct, $per_mesct, $where_liqui);
            } else {
                $this->dh21Repository->obtenerConceptosLiquidadosSicoss($per_anoct, $per_mesct, $where);
            }

            Log::info('Conceptos liquidados procesados exitosamente');

        } catch (\Exception $e) {
            Log::error('Error al procesar conceptos liquidados', [
                'error' => $e->getMessage(),
                'periodo' => "{$per_mesct}/{$per_anoct}"
            ]);
            throw $e;
        }
    }

    /**
     * Obtiene las licencias de agentes remuneradas y no remuneradas
     *
     * @param string $where Condición WHERE
     * @return array Array combinado de licencias
     */
    protected function obtenerLicenciasAgentes(string $where): array
    {
        try {
            $licencias_agentes_no_remunem = $this->licenciaRepository->getLicenciasVigentes($where);
            $licencias_agentes_remunem = $this->licenciaRepository->getLicenciasProtecintegralVacaciones($where);

            $licencias_agentes = array_merge($licencias_agentes_no_remunem, $licencias_agentes_remunem);

            Log::info('Licencias de agentes obtenidas', [
                'no_remuneradas' => count($licencias_agentes_no_remunem),
                'remuneradas' => count($licencias_agentes_remunem),
                'total' => count($licencias_agentes)
            ]);

            return $licencias_agentes;

        } catch (\Exception $e) {
            Log::error('Error al obtener licencias de agentes', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }


    /**
     * Procesa SICOSS sin períodos retro
     *
     * @param array $datos Datos de configuración
     * @param int $per_anoct Año del período
     * @param int $per_mesct Mes del período
     * @param string $where_periodo Condición WHERE del período
     * @param string $where Condición WHERE base
     * @param string $path Ruta de archivos
     * @param array $licencias_agentes Licencias de agentes
     * @param bool $retornar_datos Si debe retornar datos
     * @return mixed
     */
    protected function procesarSinRetro(
        array $datos, int $per_anoct, int $per_mesct, string $where_periodo,
        string $where, string $path, array $licencias_agentes, bool $retornar_datos
    ) {
        try {
            $nombre_arch = 'sicoss';
            $periodo = 'Vigente_sin_retro';
            self::$archivos[$periodo] = $path . $nombre_arch;

            $legajos = $this->sicossLegajoFilterRepository->obtenerLegajos(
                self::$codc_reparto, $where_periodo, $where,
                $datos['check_lic'], $datos['check_sin_activo']
            );

            $periodo_display = $per_mesct . '/' . $per_anoct . ' (Vigente)';

            if ($retornar_datos === true) {
                return $this->sicossLegajoProcessorRepository->procesarSicoss(
                    $datos, $per_anoct, $per_mesct, $legajos, $nombre_arch,
                    $licencias_agentes, $datos['check_retro'], $datos['check_sin_activo'], $retornar_datos
                );
            }

            $totales[$periodo_display] = $this->sicossLegajoProcessorRepository->procesarSicoss(
                $datos, $per_anoct, $per_mesct, $legajos, $nombre_arch,
                $licencias_agentes, $datos['check_retro'], $datos['check_sin_activo'], $retornar_datos
            );

            // Limpiar tabla temporal usando la nueva abstracción
            $this->databaseOperation->dropTemporaryTable('conceptos_liquidados');

            Log::info('Procesamiento sin retro completado', [
                'archivo' => $nombre_arch,
                'legajos_procesados' => count($legajos)
            ]);

            return $totales;

        } catch (\Exception $e) {
            Log::error('Error en procesamiento sin retro', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }


    /**
     * Procesa SICOSS con períodos retro
     *
     * @param array $datos Datos de configuración
     * @param int $per_anoct Año del período
     * @param int $per_mesct Mes del período
     * @param string $where Condición WHERE base
     * @param string $path Ruta de archivos
     * @param array $licencias_agentes Licencias de agentes
     * @param bool $retornar_datos Si debe retornar datos
     * @return array
     */
    protected function procesarConRetro(
        array $datos, int $per_anoct, int $per_mesct, string $where,
        string $path, array $licencias_agentes, bool $retornar_datos
    ): array {
        try {
            $totales = [];

            // Obtener períodos retro y el período 0-0 que será el período actual
            $periodos_retro = $this->dh21Repository->obtenerPeriodosRetro(
                $datos['check_lic'], $datos['check_retro']
            );

            Log::info('Procesando períodos retro', [
                'cantidad_periodos' => count($periodos_retro)
            ]);

            foreach ($periodos_retro as $periodo_data) {
                $totales = array_merge($totales, $this->procesarPeriodoRetro(
                    $periodo_data, $datos, $per_anoct, $per_mesct,
                    $where, $path, $licencias_agentes, $retornar_datos
                ));
            }

            return $totales;

        } catch (\Exception $e) {
            Log::error('Error en procesamiento con retro', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }


    /**
     * Procesa un período retro específico
     *
     * @param array $periodo_data Datos del período
     * @param array $datos Datos de configuración
     * @param int $per_anoct Año del período
     * @param int $per_mesct Mes del período
     * @param string $where Condición WHERE base
     * @param string $path Ruta de archivos
     * @param array $licencias_agentes Licencias de agentes
     * @param bool $retornar_datos Si debe retornar datos
     * @return array
     */
    protected function procesarPeriodoRetro(
        array $periodo_data, array $datos, int $per_anoct, int $per_mesct,
        string $where, string $path, array $licencias_agentes, bool $retornar_datos
    ): array {
        try {
            $mes = str_pad($periodo_data['mes_retro'], 2, "0", STR_PAD_LEFT);
            $where_periodo = "t.ano_retro=" . $periodo_data['ano_retro'] . " AND t.mes_retro=" . $mes;

            $legajos = $this->sicossLegajoFilterRepository->obtenerLegajos(
                self::$codc_reparto, $where_periodo, $where,
                $datos['check_lic'], $datos['check_sin_activo']
            );

            if ($periodo_data['ano_retro'] == 0 && $periodo_data['mes_retro'] == 0) {
                $resultado = $this->procesarPeriodoVigente(
                    $datos, $per_anoct, $per_mesct, $legajos, $path,
                    $licencias_agentes, $retornar_datos
                );
            } else {
                $resultado = $this->procesarPeriodoHistorico(
                    $periodo_data, $datos, $per_anoct, $per_mesct,
                    $legajos, $path, $retornar_datos
                );
            }

            // Limpiar tabla temporal usando la nueva abstracción
            $this->databaseOperation->dropTemporaryTable('conceptos_liquidados');

            return $resultado;

        } catch (\Exception $e) {
            Log::error('Error al procesar período retro', [
                'periodo' => $periodo_data,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }


    /**
     * Procesa el período vigente en el contexto de retro
     *
     * @param array $datos Datos de configuración
     * @param int $per_anoct Año del período
     * @param int $per_mesct Mes del período
     * @param array $legajos Legajos a procesar
     * @param string $path Ruta de archivos
     * @param array $licencias_agentes Licencias de agentes
     * @param bool $retornar_datos Si debe retornar datos
     * @return array
     */
    protected function procesarPeriodoVigente(
        array $datos, int $per_anoct, int $per_mesct, array $legajos,
        string $path, array $licencias_agentes, bool $retornar_datos
    ): array {
        $nombre_arch = 'sicoss_retro_periodo_vigente';
        $periodo = $per_mesct . '/' . $per_anoct;
        $item = $per_mesct . '/' . $per_anoct . ' (Vigente)';

        self::$archivos[$periodo] = $path . $nombre_arch;

        if ($retornar_datos === true) {
            return $this->sicossLegajoProcessorRepository->procesarSicoss(
                $datos, $per_anoct, $per_mesct, $legajos, $nombre_arch,
                $licencias_agentes, $datos['check_retro'], $datos['check_sin_activo'], $retornar_datos
            );
        }

        $subtotal = $this->sicossLegajoProcessorRepository->procesarSicoss(
            $datos, $per_anoct, $per_mesct, $legajos, $nombre_arch,
            $licencias_agentes, $datos['check_retro'], $datos['check_sin_activo'], $retornar_datos
        );

        return [$item => $subtotal];
    }

    /**
     * Procesa un período histórico
     *
     * @param array $periodo_data Datos del período
     * @param array $datos Datos de configuración
     * @param int $per_anoct Año del período
     * @param int $per_mesct Mes del período
     * @param array $legajos Legajos a procesar
     * @param string $path Ruta de archivos
     * @param bool $retornar_datos Si debe retornar datos
     * @return array
     */
    protected function procesarPeriodoHistorico(
        array $periodo_data, array $datos, int $per_anoct, int $per_mesct,
        array $legajos, string $path, bool $retornar_datos
    ): array {
        $nombre_arch = 'sicoss_retro_' . $periodo_data['ano_retro'] . '_' . $periodo_data['mes_retro'];
        $periodo = $periodo_data['ano_retro'] . $periodo_data['mes_retro'];
        $item = $periodo_data['mes_retro'] . "/" . $periodo_data['ano_retro'];

        self::$archivos[$periodo] = $path . $nombre_arch;

        $subtotal = $this->sicossLegajoProcessorRepository->procesarSicoss(
            $datos, $per_anoct, $per_mesct, $legajos, $nombre_arch,
            null, $datos['check_retro'], $datos['check_sin_activo'], $retornar_datos
        );

        return [$item => $subtotal];
    }

    /**
     * Limpia todas las tablas temporales utilizadas en el proceso
     *
     * @return void
     */
    protected function limpiarTablasTemporales(): void
    {
        try {
            // Lista de tablas temporales a limpiar
            $tablas_temporales = [
                'conceptos_liquidados',
                'pre_conceptos_liquidados'
            ];

            foreach ($tablas_temporales as $tabla) {
                $resultado = $this->databaseOperation->dropTemporaryTable($tabla);

                if (!$resultado) {
                    Log::warning("No se pudo eliminar la tabla temporal: {$tabla}");
                }
            }

            Log::info('Limpieza de tablas temporales completada');

        } catch (\Exception $e) {
            Log::error('Error al limpiar tablas temporales', [
                'error' => $e->getMessage()
            ]);
            // No relanzamos la excepción para no interrumpir el flujo principal
        }
    }

    /**
     * Procesa el resultado final según los parámetros de testing
     *
     * @param array $totales Totales calculados
     * @param string $testeo_directorio_salida Directorio de salida para testing
     * @param string $testeo_prefijo_archivos Prefijo de archivos para testing
     * @return mixed
     */
    protected function procesarResultadoFinal(
        array $totales,
        string $testeo_directorio_salida = '',
        string $testeo_prefijo_archivos = ''
    ) {
        try {
            // Si estamos en modo de testeo, copiar el archivo al directorio especificado
            if ($testeo_directorio_salida != '' && $testeo_prefijo_archivos != '') {
                $nombre_arch = array_key_last(self::$archivos);
                $origen = storage_path('app/comunicacion/sicoss/' . $nombre_arch . '.txt');
                $destino = $testeo_directorio_salida . '/' . $testeo_prefijo_archivos;

                if (!file_exists($origen)) {
                    Log::error('Archivo de origen no encontrado', [
                        'origen' => $origen
                    ]);
                    throw new \Exception("Archivo de origen no encontrado: {$origen}");
                }

                if (!is_dir($testeo_directorio_salida)) {
                    Log::error('Directorio de destino no encontrado', [
                        'destino' => $testeo_directorio_salida
                    ]);
                    throw new \Exception("Directorio de destino no encontrado: {$testeo_directorio_salida}");
                }

                if (!copy($origen, $destino)) {
                    Log::error('Error al copiar archivo', [
                        'origen' => $origen,
                        'destino' => $destino
                    ]);
                    throw new \Exception("Error al copiar archivo de {$origen} a {$destino}");
                }

                Log::info('Archivo copiado exitosamente para testing', [
                    'origen' => $origen,
                    'destino' => $destino
                ]);

                return true;
            } else {
                // Transformar los totales a formato recordset
                Log::info('Transformando totales a recordset', [
                    'cantidad_totales' => count($totales)
                ]);

                return $this->sicossFormateadorRepository->transformarARecordset($totales);
            }
        } catch (\Exception $e) {
            Log::error('Error al procesar resultado final', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
}
