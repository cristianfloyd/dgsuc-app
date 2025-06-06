<?php

namespace App\Services\Afip;

use Illuminate\Support\Facades\Log;
use App\Data\Sicoss\SicossProcessData;
use App\Traits\MapucheConnectionTrait;
use App\Contracts\Dh01RepositoryInterface;
use App\Contracts\Dh21RepositoryInterface;
use App\Contracts\DatabaseOperationInterface;
use App\Repositories\Sicoss\Contracts\Dh03RepositoryInterface;
use App\Repositories\Sicoss\Contracts\LicenciaRepositoryInterface;
use App\Repositories\Sicoss\Contracts\SicossEstadoRepositoryInterface;
use App\Repositories\Sicoss\Contracts\SicossCalculoRepositoryInterface;
use App\Repositories\Sicoss\Contracts\SicossFormateadorRepositoryInterface;
use App\Repositories\Sicoss\Contracts\SicossOrchestatorRepositoryInterface;
use App\Repositories\Sicoss\Contracts\SicossLegajoFilterRepositoryInterface;
use App\Repositories\Sicoss\Contracts\SicossConfigurationRepositoryInterface;
use App\Repositories\Sicoss\Contracts\SicossLegajoProcessorRepositoryInterface;

class SicossLegacy
{
    use MapucheConnectionTrait;

    protected string $codc_reparto;
    protected array $archivos = [];


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
        protected SicossOrchestatorRepositoryInterface $sicossOrchestatorRepository,
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

            // Crear el DTO con los datos recibidos
            $datosProcess = SicossProcessData::fromArray($datos);

            // Cargar configuraciones usando el nuevo repositorio
            $this->sicossConfigurationRepository->cargarConfiguraciones();

            // Obtener código de reparto desde configuración
            $this->codc_reparto = $this->sicossConfigurationRepository->getCodigoReparto();

            // Obtener período fiscal usando el nuevo repositorio
            $periodo_fiscal = $this->sicossConfigurationRepository->obtenerPeriodoFiscal();
            $per_mesct = $periodo_fiscal['mes'];
            $per_anoct = $periodo_fiscal['ano'];

            // Si los topes no están definidos, obtenerlos de la configuración
            if (!$datosProcess->TopeJubilatorioPatronal) {
                $topes = $this->sicossConfigurationRepository->getTopes();
                $datosProcess = $datosProcess->withDefaultTopes($topes);
            }

            // Generar filtros básicos usando el nuevo repositorio
            $filtros = $this->sicossConfigurationRepository->generarFiltrosBasicos($datos);
            $opcion_retro   = $filtros['opcion_retro'];
            $filtro_legajo  = $filtros['filtro_legajo'];
            $where          = $filtros['where'];
            $where_periodo  = $filtros['where_periodo'];

            // Limpiar tablas temporales previas antes de iniciar el proceso
            $this->limpiarTablasTemporales();

            //si se envia nro_liqui desde la generacion de libro de sueldo
            $this->procesarConceptosLiquidados($datosProcess, $per_anoct, $per_mesct, $where);


            // Inicializar configuración de archivos usando el nuevo repositorio
            $config_archivos = $this->sicossConfigurationRepository->inicializarConfiguracionArchivos();
            $path = $config_archivos['path'];
            $totales = $config_archivos['totales'];

            // Obtener licencias de agentes usando el nuevo repositorio
            $licencias_agentes = $this->licenciaRepository->getLicenciasVigentes($where);

            // Configurar el orquestador con el código de reparto
            $this->sicossOrchestatorRepository->setCodigoReparto($this->codc_reparto);

            // Ejecutar proceso completo usando el orquestrador
            $totales = $this->sicossOrchestatorRepository->ejecutarProcesoCompleto(
                $datosProcess,
                $periodo_fiscal,
                $filtros,
                $path,
                $licencias_agentes,
                $retornar_datos
            );

            // Limpiar tablas temporales
            $this->limpiarTablasTemporales();

            // Procesar resultado final usando el orquestador
            return $this->sicossOrchestatorRepository->procesarResultadoFinal(
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
     * @param SicossProcessData $datos Datos de configuración
     * @param int $per_anoct Año del período
     * @param int $per_mesct Mes del período
     * @param string $where Condición WHERE base
     * @return void
     */
    protected function procesarConceptosLiquidados(SicossProcessData $datos, int $per_anoct, int $per_mesct, string $where): void
    {
        try {
            // Si se envía nro_liqui desde la generación de libro de sueldo
            if ($datos->nro_liqui) {
                $where_liqui = $where . ' AND dh21.nro_liqui = ' . $datos->nro_liqui;
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
     * Limpia todas las tablas temporales utilizadas en el proceso
     *
     * @return void
     */
    protected function limpiarTablasTemporales(): void
    {
        try {
            // Lista de tablas temporales a limpiar
            $tablas_temporales = [
                'pre_conceptos_liquidados',
                'conceptos_liquidados'
            ];

            foreach ($tablas_temporales as $tabla) {
                try {
                    $resultado = $this->databaseOperation->dropTemporaryTable($tabla);
                    Log::debug("Tabla temporal eliminada: {$tabla}", ['resultado' => $resultado]);
                } catch (\Exception $e) {
                    // Ignorar errores si la tabla no existe
                    Log::debug("Tabla temporal no existía o no se pudo eliminar: {$tabla}", [
                        'error' => $e->getMessage()
                    ]);
                }
            }

            Log::info('Limpieza de tablas temporales completada');

        } catch (\Exception $e) {
            Log::error('Error general al limpiar tablas temporales', [
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
                $nombre_arch = array_key_last($this->archivos);
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
