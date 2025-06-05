<?php

namespace App\Services\Afip;

use App\Models\Dh01;
use App\Models\Dh03;
use Illuminate\Support\Facades\DB;
use App\Models\Mapuche\MapucheConfig;
use App\Traits\MapucheConnectionTrait;
use App\Services\Mapuche\LicenciaService;
use App\Contracts\Dh01RepositoryInterface;
use App\Contracts\Dh21RepositoryInterface;
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
    protected static $aportes_voluntarios;
    protected static $codigo_os_aporte_adicional;
    protected static $codigo_obrasocial_fc;
    protected static $codigo_obra_social_default;
    protected static $hs_extras_por_novedad;
    protected static $tipoEmpresa;
    protected static $asignacion_familiar;
    protected static $trabajadorConvencionado;
    protected static $codc_reparto;
    protected static $porc_aporte_adicional_jubilacion;
    protected static $cantidad_adherentes_sicoss;
    protected static $archivos;
    protected static $categoria_diferencial;


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
        protected SicossLegajoProcessorRepositoryInterface $sicossLegajoProcessorRepository
    ) {}



    public function genera_sicoss($datos, $testeo_directorio_salida = '', $testeo_prefijo_archivos = '', $retornar_datos = FALSE)
    {
        // Cargar configuraciones usando el nuevo repositorio
        $this->sicossConfigurationRepository->cargarConfiguraciones();

        // Obtener período fiscal usando el nuevo repositorio
        $periodo_fiscal = $this->sicossConfigurationRepository->obtenerPeriodoFiscal();
        $per_mesct = $periodo_fiscal['mes'];
        $per_anoct = $periodo_fiscal['ano'];

        // Generar filtros básicos usando el nuevo repositorio
        $filtros = $this->sicossConfigurationRepository->generarFiltrosBasicos($datos);
        $opcion_retro = $filtros['opcion_retro'];
        $filtro_legajo = $filtros['filtro_legajo'];
        $where = $filtros['where'];
        $where_periodo = $filtros['where_periodo'];

        //si se envia nro_liqui desde la generacion de libro de sueldo
        $dh21Repository = app(Dh21RepositoryInterface::class);
        if (isset($datos['nro_liqui'])) {
            $where_liqui = $where . ' AND dh21.nro_liqui = ' . $datos['nro_liqui'];
            $dh21Repository->obtenerConceptosLiquidadosSicoss($per_anoct, $per_mesct, $where_liqui);
        } else {
            $dh21Repository->obtenerConceptosLiquidadosSicoss($per_anoct, $per_mesct, $where);
        }

        // Inicializar configuración de archivos usando el nuevo repositorio
        $config_archivos = $this->sicossConfigurationRepository->inicializarConfiguracionArchivos();
        $path = $config_archivos['path'];
        $totales = $config_archivos['totales'];

        $licenciaRepository = app(LicenciaRepositoryInterface::class);
        $licencias_agentes_no_remunem = $licenciaRepository->getLicenciasVigentes($where);
        $licencias_agentes_remunem = $licenciaRepository->getLicenciasProtecintegralVacaciones($where);
        $licencias_agentes = array_merge($licencias_agentes_no_remunem, $licencias_agentes_remunem);

        // Si no tengo tildado el check el proceso genera un unico archivo sin tener en cuenta a�o y mes retro
        if ($opcion_retro == 0) {
            $nombre_arch              = 'sicoss';
            $periodo                  = 'Vigente_sin_retro';
            self::$archivos[$periodo] = $path . $nombre_arch;
            $legajos            = $this->sicossLegajoFilterRepository->obtenerLegajos(self::$codc_reparto, $where_periodo, $where, $datos['check_lic'], $datos['check_sin_activo']);
            $periodo            = $per_mesct . '/' . $per_anoct . ' (Vigente)';
            if ($retornar_datos === TRUE)
                return $this->sicossLegajoProcessorRepository->procesarSicoss($datos, $per_anoct, $per_mesct, $legajos, $nombre_arch, $licencias_agentes, $datos['check_retro'], $datos['check_sin_activo'], $retornar_datos);
            $totales[$periodo] = $this->sicossLegajoProcessorRepository->procesarSicoss($datos, $per_anoct, $per_mesct, $legajos, $nombre_arch, $licencias_agentes, $datos['check_retro'], $datos['check_sin_activo'], $retornar_datos);
            $sql     =  "DROP TABLE IF EXISTS conceptos_liquidados";
            DB::connection($this->getConnectionName())->execute($sql);
        } else {
            // Si tengo tildada la opcion lo que se genera es un archivo por cada periodo retro y uno para los que tiene a�o y mes retro en cero,
            // o sea, se particiona la tabla temporal que se obtiene en obtener_conceptos_liquidados
            // Periodos retro y el periodo 0-0 que va ser el periodo actual
            $periodos_retro = $dh21Repository->obtenerPeriodosRetro($datos['check_lic'], $datos['check_retro']);

            for ($i = 0; $i < count($periodos_retro); $i++) {
                $p             = $periodos_retro[$i];
                $mes           = str_pad($p['mes_retro'], 2, "0", STR_PAD_LEFT);
                //agrego cero adelante a meses
                $where_periodo = "t.ano_retro=" . $p['ano_retro'] . " AND t.mes_retro=" . $mes;
                $legajos = $this->sicossLegajoFilterRepository->obtenerLegajos(self::$codc_reparto, $where_periodo, $where, $datos['check_lic'], $datos['check_sin_activo']);

                if ($p['ano_retro'] == 0 && $p['mes_retro'] == 0) {
                    $nombre_arch  = 'sicoss_retro_periodo_vigente';
                    $periodo      = $per_mesct . '/' . $per_anoct;
                    $item         = $per_mesct . '/' . $per_anoct . ' (Vigente)';
                    if ($retornar_datos === TRUE)
                        return   $this->sicossLegajoProcessorRepository->procesarSicoss($datos, $per_anoct, $per_mesct, $legajos, $nombre_arch, $licencias_agentes, $datos['check_retro'], $datos['check_sin_activo'], $retornar_datos);
                    $subtotal  = $this->sicossLegajoProcessorRepository->procesarSicoss($datos, $per_anoct, $per_mesct, $legajos, $nombre_arch, $licencias_agentes, $datos['check_retro'], $datos['check_sin_activo'], $retornar_datos);
                } else {
                    $nombre_arch = 'sicoss_retro_' . $p['ano_retro'] . '_' . $p['mes_retro'];
                    $periodo     = $p['ano_retro'] . $p['mes_retro'];
                    $item        = $p['mes_retro'] . "/" . $p['ano_retro'];
                    $subtotal  = $this->sicossLegajoProcessorRepository->procesarSicoss($datos, $per_anoct, $per_mesct, $legajos, $nombre_arch, NULL, $datos['check_retro'], $datos['check_sin_activo'], $retornar_datos);
                }

                self::$archivos[$periodo] = $path . $nombre_arch;

                // Elimino tabla temporal
                $sql  =  "DROP TABLE IF EXISTS conceptos_liquidados";
                DB::connection($this->getConnectionName())->execute($sql);

                $totales[$item] = $subtotal;
            }
        }

        // Elimino tabla temporal
        $sql = "DROP TABLE IF EXISTS pre_conceptos_liquidados";
        DB::connection($this->getConnectionName())->execute($sql);

        if ($testeo_directorio_salida != '' && $testeo_prefijo_archivos != '') {
            copy(storage_path('app/comunicacion/sicoss/' . $nombre_arch . '.txt'), $testeo_directorio_salida . '/' . $testeo_prefijo_archivos);
        } else {
            return $this->sicossFormateadorRepository->transformarARecordset($totales);
        }
    }

}
