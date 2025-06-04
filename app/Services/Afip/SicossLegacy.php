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
        protected SicossEstadoRepositoryInterface $sicossEstadoRepository
    ) {}
















    public function obtener_legajos($codc_reparto, $where_periodo_retro, $where_legajo = ' true ', $check_lic = false, $check_sin_activo = false)
    {
        // Si la opcion no tiene en cuenta los retroactivos el proceso es como se venia haciendo, se toma de la tabla anterior y se vuelca sobre un unico archivo
        // Si hay que tener en cuenta los retros se toma la tabla anterior y se segmenta por periodo retro, se genera un archivo por cada segmento

        $sql_conceptos_liq_filtrados = "
                                            SELECT
                                                    *
                                            INTO TEMP
                                                    conceptos_liquidados
                                            FROM
                                                pre_conceptos_liquidados t
                                            WHERE
                                            $where_periodo_retro
            ";

        $rs_filtrado = DB::connection($this->getConnectionName())->select($sql_conceptos_liq_filtrados);


        $sql_ix = "CREATE INDEX ix_conceptos_liquidados_1 ON conceptos_liquidados(nro_legaj,tipos_grupos);";
        $rs_filtrado = DB::connection($this->getConnectionName())->select($sql_ix);
        $sql_ix = "CREATE INDEX ix_conceptos_liquidados_2 ON conceptos_liquidados(nro_legaj,tipo_conce);";
        $rs_filtrado = DB::connection($this->getConnectionName())->select($sql_ix);

        // Se obtienen datos por legajo, de los numeros de legajos liquidados en la tabla anterior conceptos_liquidados
        // si en los datos del legajo licencia es igual a cero es que el legajo no tenia licencias o no algun concepto liquidado
        $dh01Repository = app(Dh01RepositoryInterface::class);
        $sql_datos_legajo = $dh01Repository->getSqlLegajos('conceptos_liquidados', 0, 'true', $codc_reparto);

        // Si tengo el check de licencias agrego a la cantidad de agentes a procesar a los agentes sin licencias sin goce
        // Si tengo el check de licencias y ademas tengo el check de retros, debo tener en cuenta las licencias solo en el archivo generado con mes y a�o 0 (son del periodo vigente)
        // tendre en cuenta licencias en el caso general (true) y cuando tenga retros y el where tenga 0-0 (vigente)
        if ($check_lic && ($where_periodo_retro == ' true ' || $where_periodo_retro == 't.ano_retro=0 AND t.mes_retro=00')) {
            // Me fijo cuales son todos los agentes con licencias sin goce (de cargo o de legajo, liquidados o no). Si habia seleccionado legajo tambien filtro
            $legajos_lic = LicenciaService::getLegajosLicenciasSinGoce($where_legajo);
            // Preparo arreglo para usar en sql IN
            $legajos_lic = trim($legajos_lic['licencias_sin_goce'], "{,}");

            // Agrego a la consulta anterior la union, para reutilizar el sql y como necesito los mismos datos parametrizo el string
            $tabla = 'dh01';
            $where = ' true ';
            if (isset($legajos_lic) && !empty($legajos_lic)) {
                $where  = ' dh01.nro_legaj IN (' . $legajos_lic . ')';
                if (!$check_lic)
                    $where .=  ' AND dh01.nro_legaj NOT IN (SELECT nro_legaj FROM conceptos_liquidados))';
                else
                    $where .= ' )';
                // si tengo licencias consulto la union de legajos. Ordeno por agente, luego de obtener todos los legajos
                $sql_datos_lic = ' UNION (' . $dh01Repository->getSqlLegajos("mapuche.dh01", 1, $where, $codc_reparto) . ' ORDER BY apyno';

                $legajos = DB::connection($this->getConnectionName())->select($sql_datos_legajo . $sql_datos_lic);
            } else {
                $sql_datos_legajo .= ' ORDER BY apyno';
                // Si no hay licencias sin goce que cumpaln con las restricciones hago el proceso comun
                $legajos = DB::connection($this->getConnectionName())->select($sql_datos_legajo);
            }
        } else {
            $sql_datos_legajo .= ' ORDER BY apyno';
            // Si no tengo el check licencias se consulta solo contra conceptos liquidados
            $legajos = DB::connection($this->getConnectionName())->select($sql_datos_legajo);
        }

        //Si esta chequeado "Generar Agentes Activos sin Cargo Activo y sin Liquidaci�n para Reserva de Puesto"
        if ($check_sin_activo) {
            $where_no_liquidado = "
                                    NOT EXISTS (SELECT 1
                                                FROM
                                                    mapuche.dh21
                                                WHERE
                                                    dh21.nro_legaj = dh01.nro_legaj
                                                )
                                    AND
                                    dh01.tipo_estad = 'A' AND NOT EXISTS 	(  	SELECT
                                                                            1
                                                                    FROM
                                                                            mapuche.dh03 car
                                                                    WHERE
                                                                            car.nro_legaj = dh01.nro_legaj AND  mapuche.map_es_cargo_activo(car.nro_cargo) )
                ";

            $sql_legajos_no_liquidados = $dh01Repository->getSqlLegajos("mapuche.dh01", 0, $where_no_liquidado, $codc_reparto);
            $legajos_t = DB::connection($this->getConnectionName())->select($sql_legajos_no_liquidados);
            $legajos = array_merge($legajos, $legajos_t);
        }

        // Elimino legajos repetidos
        $legajos_sin_repetidos = array();
        foreach ($legajos as $legajo) {
            if (isset($legajos_sin_repetidos[$legajo['nro_legaj']])) {
                if ($legajos_sin_repetidos[$legajo['nro_legaj']]['licencia'] == 1)
                    $legajos_sin_repetidos[$legajo['nro_legaj']] = $legajo;
            } else
                $legajos_sin_repetidos[$legajo['nro_legaj']] = $legajo;
        }
        $legajos = array();
        foreach ($legajos_sin_repetidos as $legajo)
            $legajos[] = $legajo;

        return $legajos;
    }



    public function genera_sicoss($datos, $testeo_directorio_salida = '', $testeo_prefijo_archivos = '', $retornar_datos = FALSE)
    {
        // Se necesita filtrar datos del periodo vigente

        $per_mesct     = MapucheConfig::getMesFiscal();
        $per_anoct     = MapucheConfig::getAnioFiscal();

        // Seteo valores de rrhhini
        self::$codigo_obra_social_default = MapucheConfig::getDefaultsObraSocial();
        self::$aportes_voluntarios        = MapucheConfig::getTopesJubilacionVoluntario();
        self::$codigo_os_aporte_adicional = MapucheConfig::getConceptosObraSocialAporteAdicional();
        self::$codigo_obrasocial_fc       = MapucheConfig::getConceptosObraSocialFliarAdherente();                   // concepto seteado en rrhhini bajo el cual se liquida el familiar a cargo
        self::$tipoEmpresa                = MapucheConfig::getDatosUniversidadTipoEmpresa();
        self::$cantidad_adherentes_sicoss = MapucheConfig::getConceptosInformarAdherentesSicoss();                   // Seg�n sea cero o uno informa datos de dh09 o se fija si existe un cpncepto liquidado bajo el concepto de codigo_obrasocial_fc
        self::$asignacion_familiar        = MapucheConfig::getConceptosAcumularAsigFamiliar();                 // Si es uno se acumulan las asiganciones familiares en Asignacion Familiar en Remuneraci�n Total (importe Bruto no imponible)
        self::$trabajadorConvencionado    = MapucheConfig::getDatosUniversidadTrabajadorConvencionado();
        self::$codc_reparto                     = MapucheConfig::getDatosCodcReparto();
        self::$porc_aporte_adicional_jubilacion = MapucheConfig::getPorcentajeAporteDiferencialJubilacion();
        self::$hs_extras_por_novedad      = MapucheConfig::getSicossHorasExtrasNovedades();   // Lee el valor HorasExtrasNovedades de RHHINI que determina si es verdadero se suman los valores de las novedades y no el importe.
        self::$categoria_diferencial       = MapucheConfig::getCategoriasDiferencial(); //obtengo las categorias seleccionadas en configuracion

        $opcion_retro  = $datos['check_retro'];
        if (isset($datos['nro_legaj'])) {
            $filtro_legajo = $datos['nro_legaj'];
        }
        self::$codc_reparto  = MapucheConfig::getDatosCodcReparto();


        // Si no filtro por n�mero de legajo => obtengo todos los legajos
        $where = ' true ';
        if (!empty($filtro_legajo))
            $where = 'dh01.nro_legaj= ' . $filtro_legajo . ' ';

        $where_periodo = ' true ';

        //si se envia nro_liqui desde la generacion de libro de sueldo
        $dh21Repository = app(Dh21RepositoryInterface::class);
        if (isset($datos['nro_liqui'])) {
            $where_liqui = $where . ' AND dh21.nro_liqui = ' . $datos['nro_liqui'];
            $dh21Repository->obtenerConceptosLiquidadosSicoss($per_anoct, $per_mesct, $where_liqui);
        } else {
            $dh21Repository->obtenerConceptosLiquidadosSicoss($per_anoct, $per_mesct, $where);
        }

        $path = storage_path('app/comunicacion/sicoss/');
        self::$archivos = array();
        $totales = array();

        $licenciaRepository = app(LicenciaRepositoryInterface::class);
        $licencias_agentes_no_remunem = $licenciaRepository->getLicenciasVigentes($where);
        $licencias_agentes_remunem = $licenciaRepository->getLicenciasProtecintegralVacaciones($where);
        $licencias_agentes = array_merge($licencias_agentes_no_remunem, $licencias_agentes_remunem);

        // Si no tengo tildado el check el proceso genera un unico archivo sin tener en cuenta a�o y mes retro
        if ($opcion_retro == 0) {
            $nombre_arch              = 'sicoss';
            $periodo                  = 'Vigente_sin_retro';
            self::$archivos[$periodo] = $path . $nombre_arch;
            $legajos            = $this->obtener_legajos(self::$codc_reparto, $where_periodo, $where, $datos['check_lic'], $datos['check_sin_activo']);
            $periodo            = $per_mesct . '/' . $per_anoct . ' (Vigente)';
            if ($retornar_datos === TRUE)
                return self::procesa_sicoss($datos, $per_anoct, $per_mesct, $legajos, $nombre_arch, $licencias_agentes, $datos['check_retro'], $datos['check_sin_activo'], $retornar_datos);
            $totales[$periodo] = self::procesa_sicoss($datos, $per_anoct, $per_mesct, $legajos, $nombre_arch, $licencias_agentes, $datos['check_retro'], $datos['check_sin_activo'], $retornar_datos);
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
                $legajos = $this->obtener_legajos(self::$codc_reparto, $where_periodo, $where, $datos['check_lic'], $datos['check_sin_activo']);

                if ($p['ano_retro'] == 0 && $p['mes_retro'] == 0) {
                    $nombre_arch  = 'sicoss_retro_periodo_vigente';
                    $periodo      = $per_mesct . '/' . $per_anoct;
                    $item         = $per_mesct . '/' . $per_anoct . ' (Vigente)';
                    if ($retornar_datos === TRUE)
                        return self::procesa_sicoss($datos, $per_anoct, $per_mesct, $legajos, $nombre_arch, $licencias_agentes, $datos['check_retro'], $datos['check_sin_activo'], $retornar_datos);
                    $subtotal  = self::procesa_sicoss($datos, $per_anoct, $per_mesct, $legajos, $nombre_arch, $licencias_agentes, $datos['check_retro'], $datos['check_sin_activo'], $retornar_datos);
                } else {
                    $nombre_arch = 'sicoss_retro_' . $p['ano_retro'] . '_' . $p['mes_retro'];
                    $periodo     = $p['ano_retro'] . $p['mes_retro'];
                    $item        = $p['mes_retro'] . "/" . $p['ano_retro'];
                    $subtotal  = self::procesa_sicoss($datos, $per_anoct, $per_mesct, $legajos, $nombre_arch, NULL, $datos['check_retro'], $datos['check_sin_activo'], $retornar_datos);
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
            return self::transformar_a_recordset($totales);
        }
    }











    public function procesa_sicoss($datos, $per_anoct, $per_mesct, $legajos, $nombre_arch, $licencias = NULL, $retro = FALSE, $check_sin_activo = FALSE, $retornar_datos = FALSE)
    {
        // Valores obtenidos del form (que se obtienen de rrhhini)
        // Topes
        $TopeJubilatorioPatronal   = $datos['TopeJubilatorioPatronal'];
        $TopeJubilatorioPersonal    = $datos['TopeJubilatorioPersonal'];
        $TopeOtrosAportesPersonales = $datos['TopeOtrosAportesPersonal'];
        $trunca_tope                = $datos['truncaTope'];
        $TopeSACJubilatorioPers     = $TopeJubilatorioPersonal / 2;
        $TopeSACJubilatorioPatr     = $TopeJubilatorioPatronal / 2;
        $TopeSACJubilatorioOtroAp   = $TopeOtrosAportesPersonales / 2;

        // Inicializo para guardar el total de cada tipo de importe para luego mostrar en informe de control
        $total = [];
        $total['bruto']       = 0;
        $total['imponible_1'] = 0;
        $total['imponible_2'] = 0;
        $total['imponible_4'] = 0;
        $total['imponible_5'] = 0;
        $total['imponible_6'] = 0; //bruto + sac docente
        $total['imponible_8'] = 0;
        $total['imponible_9'] = 0;
        $legajos_validos = [];
        $j = 0;

        // En este for se completan los campos necesarios para cada uno de los legajos liquidados
        for ($i = 0; $i < count($legajos); $i++) {
            $legajo = $legajos[$i]['nro_legaj'];

            $legajos[$i]['ImporteSACOtroAporte'] = 0;
            $legajos[$i]['TipoDeOperacion']      = 0;
            $legajos[$i]['ImporteImponible_4']   = 0;
            $legajos[$i]['ImporteSACNoDocente']  = 0;

            $legajos[$i]['ImporteSACDoce']  = 0;
            $legajos[$i]['ImporteSACAuto']  = 0;

            $legajos[$i]['codigo_os'] = $this->sicossCalculoRepository->codigoOs($legajo);

            //#44909 Incorporar a la salida de SICOSS el c�digo de situaci�n Reserva de Puesto (14)
            if ($check_sin_activo) {
                $where_not_dh21 = "
                                    NOT EXISTS (SELECT 1
                                                            FROM
                                                                mapuche.dh21
                                                            WHERE
                                                                dh21.nro_legaj = l.nro_legaj
                                                            )
                                    AND l.nro_legaj = $legajo
                    ";
                $legajo_sin_liquidar = Dh01::getLegajosActivosSinCargosVigentes($where_not_dh21);

                if (isset($legajo_sin_liquidar[0])) {
                    if ($legajo_sin_liquidar[0]['nro_legaj'] == $legajo)
                        $legajos[$i]['codigosituacion'] = 14;
                }
            }

            if (!$retro) {
                $dh03Repository = app(Dh03RepositoryInterface::class);
                $limites = $dh03Repository->getLimitesCargos($legajo);
                //En caso de que el agente no tenga cargos activos, pero aparezca liquidado.
                if (!isset($limites[0]['maximo'])) {
                    $cargos_activos_agente = Dh03::getCargosActivos($legajo);
                    if (empty($cargos_activos_agente)) {
                        $fecha_fin = MapucheConfig::getFechaFinPeriodoCorriente();
                        $limites[0]['maximo'] = substr($fecha_fin, 9, 2);
                    }
                }
                $estado_situacion = $this->sicossEstadoRepository->inicializarEstadoSituacion($legajos[$i]['codigosituacion'], $limites[0]['minimo'], $limites[0]['maximo']);
                $cargos_legajo = $dh03Repository->getCargosActivosSinLicencia($legajo);
                $cargos_legajo2 = $dh03Repository->getCargosActivosConLicenciaVigente($legajo);
                $cargos_legajo = array_merge($cargos_legajo, $cargos_legajo2);
                // En el caso de las licencias de legajo, se mantiene el c�digo de condici�n en esos d�as
                // que corresponde al tipo de licencia (5 => maternidad o 13 => no remunerada)
                // Se considera que no se puede superponer con otra licencia
                $dias_lic_legajo = [];

                // Se evaluan las licencias
                if ($licencias != NULL) {

                    foreach ($licencias as $licencia) {
                        if ($licencia['nro_legaj'] == $legajo) {
                            for ($dia = $licencia['inicio']; $dia <= $licencia['final']; $dia++) {
                                if (!in_array($dia, $dias_lic_legajo)) { // Los d�as con licencia de legajo no se tocan
                                    if ($limites[0]['maximo'] >= $dia)
                                        $estado_situacion[$dia] = $this->sicossEstadoRepository->evaluarCondicionLicencia($estado_situacion[$dia], $licencia['condicion']);
                                    if ($licencia['es_legajo']) {
                                        $dias_lic_legajo[] = $dia; // En este d�a cuenta con licencia de legajo
                                    }
                                }
                            }
                        }
                    }
                }

                $licencias_cargos = [];
                foreach ($cargos_legajo as $cargo) {
                    $fin_mes = $day = date("d", mktime(0, 0, 0, MapucheConfig::getMesFiscal() + 1, 0, date("Y")));
                    for ($ini_mes = 1; $ini_mes <= $fin_mes; $ini_mes++) {
                        if (!isset($licencias_cargos[$cargo['nro_cargo']][$i]))
                            $licencias_cargos[$cargo['nro_cargo']][$ini_mes] = 1;

                        if ((isset($cargo['inicio_lic']) && isset($cargo['final_lic'])) && $ini_mes >= $cargo['inicio_lic'] && $ini_mes <= $cargo['final_lic'])
                            $licencias_cargos[$cargo['nro_cargo']][$ini_mes] = $cargo['condicion'];
                        else
                            $licencias_cargos[$cargo['nro_cargo']][$ini_mes] = 1;
                    }
                }

                // Se evaluan los cargos
                foreach ($licencias_cargos as $cargo) {
                    for ($dia = 1; $dia <= count($cargo); $dia++) {
                        if (!in_array($dia, $dias_lic_legajo)) {
                            if ((isset($estado_situacion[$dia]) && $estado_situacion[$dia] == 13)) {
                                $estado_situacion[$dia] = $cargo[$dia]; // Si estaba trabajando en alg�n cargo se prioriza el c�digo en dha8
                            }
                        }
                    }
                }

                $cambios_estado = $this->sicossEstadoRepository->calcularCambiosEstado($estado_situacion);
                $dias_trabajados = $this->sicossEstadoRepository->calcularDiasTrabajados($estado_situacion);
                $revista_legajo = $this->sicossEstadoRepository->calcularRevistaLegajo($cambios_estado);


                // Como c�digo de situaci�n general se toma el �ltimo (?)
                $legajos[$i]['codigosituacion'] = $estado_situacion[$limites[0]['maximo']];
                // Revista 1
                $legajos[$i]['codigorevista1'] = $revista_legajo[1]['codigo'];
                $legajos[$i]['fecharevista1'] = $revista_legajo[1]['dia'];
                // Revista 2
                if ($revista_legajo[2]['codigo'] == 0) {
                    $legajos[$i]['codigorevista2'] = $revista_legajo[1]['codigo'];
                } else {
                    $legajos[$i]['codigorevista2'] = $revista_legajo[2]['codigo'];
                }
                $legajos[$i]['fecharevista2'] = $revista_legajo[2]['dia'];

                // Revista 3
                if ($revista_legajo[3]['codigo'] == 0) {
                    $legajos[$i]['codigorevista3'] = $legajos[$i]['codigorevista2'];
                } else {
                    $legajos[$i]['codigorevista3'] = $revista_legajo[3]['codigo'];
                }
                $legajos[$i]['fecharevista3'] = $revista_legajo[3]['dia'];

                // Como d�as trabajados se toman aquellos d�as de cargo menos los d�as de licencia sin goce (?)
                $legajos[$i]['dias_trabajados'] = $dias_trabajados;
            } else {
                // Se evaluan

                // Si tiene una licencia por maternidad activa el codigo de situacion es 5
                if (LicenciaService::tieneLicenciaMaternidadActiva($legajo)) {
                    $legajos[$i]['codigosituacion'] = 5;
                }

                // Si tengo chequeado el tilde de licencias cambio el codigo de situacion y la cantidad de dias trabajados se vuelve 0
                if ($datos['check_lic'] && ($legajos[$i]['licencia'] == 1)) {
                    $legajos[$i]['codigosituacion'] = 13;
                    $legajos[$i]['dias_trabajados'] = '00';
                } else {
                    $legajos[$i]['dias_trabajados'] = '30';
                }

                $legajos[$i]['codigorevista1'] = $legajos[$i]['codigosituacion'];
                $legajos[$i]['fecharevista1'] = '01';
                $legajos[$i]['codigorevista2'] = '00';
                $legajos[$i]['fecharevista2'] = '00';
                $legajos[$i]['codigorevista3'] = '00';
                $legajos[$i]['fecharevista3'] = '00';
            }

            // Se informa solo si tiene conyugue o no; no la cantidad
            if ($legajos[$i]['conyugue'] > 0)
                $legajos[$i]['conyugue'] = 1;

            // --- Obtengo la sumarizaci�n seg�n concepto � tipo de grupo de un concepto ---
            $this->sumarizar_conceptos_por_tipos_grupos($legajo, $legajos[$i]);

            // --- Otros datos remunerativos ---

            // Sumarizar conceptos segun tipo de concepto
            $suma_conceptos_tipoC = $this->sicossCalculoRepository->calcularRemunerGrupo($legajo, 'C', 'nro_orimp >0 AND codn_conce > 0');
            $suma_conceptos_tipoF = $this->sicossCalculoRepository->calcularRemunerGrupo($legajo, 'F', 'true');

            $legajos[$i]['Remuner78805']               = $suma_conceptos_tipoC;
            $legajos[$i]['AsignacionesFliaresPagadas'] = $suma_conceptos_tipoF;
            $legajos[$i]['ImporteImponiblePatronal']   = $suma_conceptos_tipoC;

            // Para calcular Remuneracion total= IMPORTE_BRUTO
            $legajos[$i]['DiferenciaSACImponibleConTope'] = 0;
            $legajos[$i]['DiferenciaImponibleConTope']    = 0;
            $legajos[$i]['ImporteSACPatronal']            = $legajos[$i]['ImporteSAC'];
            $legajos[$i]['ImporteImponibleSinSAC']        = $legajos[$i]['ImporteImponiblePatronal'] - $legajos[$i]['ImporteSACPatronal'];
            if ($legajos[$i]['ImporteSAC'] > $TopeSACJubilatorioPatr  && $trunca_tope == 1) {
                $legajos[$i]['DiferenciaSACImponibleConTope'] = $legajos[$i]['ImporteSAC'] - $TopeSACJubilatorioPatr;
                $legajos[$i]['ImporteImponiblePatronal']  -= $legajos[$i]['DiferenciaSACImponibleConTope'];
                $legajos[$i]['ImporteSACPatronal']         = $TopeSACJubilatorioPatr;
            }

            if ($legajos[$i]['ImporteImponibleSinSAC'] > $TopeJubilatorioPatronal && $trunca_tope == 1) {
                $legajos[$i]['DiferenciaImponibleConTope'] = $legajos[$i]['ImporteImponibleSinSAC'] - $TopeJubilatorioPatronal;
                $legajos[$i]['ImporteImponiblePatronal']  -= $legajos[$i]['DiferenciaImponibleConTope'];
            }

            $legajos[$i]['IMPORTE_BRUTO'] = $legajos[$i]['ImporteImponiblePatronal'] + $legajos[$i]['ImporteNoRemun'];

            // Para calcular IMPORTE_IMPON que es lo mismo que importe imponible 1
            $legajos[$i]['IMPORTE_IMPON'] = 0;
            $legajos[$i]['IMPORTE_IMPON'] = $suma_conceptos_tipoC;

            $VerificarAgenteImportesCERO  = 1;

            // Si es el check de informar becarios en configuracion esta chequeado entonces sumo al importe imponible la suma de conceptos de ese tipo de grupo (Becarios ART)
            if ($legajos[$i]['ImporteImponibleBecario'] != 0) {
                $legajos[$i]['IMPORTE_IMPON']            += $legajos[$i]['ImporteImponibleBecario'];
                $legajos[$i]['IMPORTE_BRUTO']            += $legajos[$i]['ImporteImponibleBecario'];
                $legajos[$i]['ImporteImponiblePatronal'] += $legajos[$i]['ImporteImponibleBecario'];
                $legajos[$i]['Remuner78805']             += $legajos[$i]['ImporteImponibleBecario'];
            }

            if ($this->sicossEstadoRepository->verificarAgenteImportesCero($legajos[$i]) == 1 || $legajos[$i]['codigosituacion'] == 5 || $legajos[$i]['codigosituacion'] == 11) // codigosituacion=5 y codigosituacion=11 quiere decir maternidad y debe infrormarse
            {
                $legajos[$i]['PorcAporteDiferencialJubilacion'] = self::$porc_aporte_adicional_jubilacion;
                $legajos[$i]['ImporteImponible_4']              = $legajos[$i]['IMPORTE_IMPON'];
                $legajos[$i]['ImporteSACNoDocente']             = 0;
                //ImporteImponible_6 viene con valor de funcion sumarizar_conceptos_por_tipos_grupos
                $legajos[$i]['ImporteImponible_6']              = round((($legajos[$i]['ImporteImponible_6'] * 100) / $legajos[$i]['PorcAporteDiferencialJubilacion']), 2);
                $Imponible6_aux                                 = $legajos[$i]['ImporteImponible_6'];
                if ($Imponible6_aux != 0) {
                    if (
                        (int)$Imponible6_aux != (int)$legajos[$i]['IMPORTE_IMPON']
                        && (abs($Imponible6_aux - $legajos[$i]['IMPORTE_IMPON'])) > 5 //redondear hasta + � - $5
                        && $legajos[$i]['ImporteImponible_6'] < $legajos[$i]['IMPORTE_IMPON']
                    ) {
                        $legajos[$i]['TipoDeOperacion']     = 2;
                        $legajos[$i]['IMPORTE_IMPON']       = $legajos[$i]['IMPORTE_IMPON'] - $legajos[$i]['ImporteImponible_6'];
                        $legajos[$i]['ImporteSACNoDocente'] = $legajos[$i]['ImporteSAC'] - $legajos[$i]['SACInvestigador'];
                    } else {
                        if ((($Imponible6_aux + 5) > $legajos[$i]['IMPORTE_IMPON'])
                            && (($Imponible6_aux - 5) < $legajos[$i]['IMPORTE_IMPON'])
                        ) {
                            $legajos[$i]['ImporteImponible_6'] = $legajos[$i]['IMPORTE_IMPON'];
                        } else {
                            $legajos[$i]['ImporteImponible_6'] = $Imponible6_aux;
                        }
                        $legajos[$i]['TipoDeOperacion']     = 1;
                        $legajos[$i]['ImporteSACNoDocente'] = $legajos[$i]['ImporteSAC'];
                    }
                } else {
                    $legajos[$i]['TipoDeOperacion']     = 1;
                    $legajos[$i]['ImporteSACNoDocente'] = $legajos[$i]['ImporteSAC'];
                }

                $legajos[$i]['ImporteSACOtroAporte']          = $legajos[$i]['ImporteSAC'];
                $legajos[$i]['DiferenciaSACImponibleConTope'] = 0;
                $legajos[$i]['DiferenciaImponibleConTope']    = 0;

                /*****************/

                $tope_jubil_personal = $TopeJubilatorioPersonal;
                if ($legajos[$i]['ImporteSAC'] > 0)
                    $tope_jubil_personal = $TopeJubilatorioPersonal + $TopeSACJubilatorioPers;


                if ($legajos[$i]['ImporteSACNoDocente']  > $tope_jubil_personal) {
                    if ($trunca_tope == 1) {
                        $legajos[$i]['DiferenciaSACImponibleConTope'] = $legajos[$i]['ImporteSACNoDocente']  - $TopeSACJubilatorioPers;
                        $legajos[$i]['IMPORTE_IMPON']                -= $legajos[$i]['DiferenciaSACImponibleConTope'];
                        $legajos[$i]['ImporteSACNoDocente']           = $TopeSACJubilatorioPers;
                    }
                } else {

                    if ($trunca_tope == 1) {

                        $bruto_nodo_sin_sac = $legajos[$i]['IMPORTE_BRUTO'] - $legajos[$i]['ImporteImponible_6'] - $legajos[$i]['ImporteSACNoDocente'];

                        $sac = $legajos[$i]['ImporteSACNoDocente'];

                        $tope = min($bruto_nodo_sin_sac, $TopeJubilatorioPersonal) + min($sac, $TopeSACJubilatorioPers);
                        $imp_1 =  $legajos[$i]['IMPORTE_BRUTO'] -  $legajos[$i]['ImporteImponible_6'];

                        $tope_sueldo = min($bruto_nodo_sin_sac - $legajos[$i]['ImporteNoRemun'], $TopeJubilatorioPersonal);
                        $tope_sac = min($sac, $TopeSACJubilatorioPers);


                        $legajos[$i]['IMPORTE_IMPON'] = min($bruto_nodo_sin_sac - $legajos[$i]['ImporteNoRemun'], $TopeJubilatorioPersonal) + min($sac, $TopeSACJubilatorioPers);
                    }
                }

                $explode = explode(',', self::$categoria_diferencial ?? ''); //arma el array
                $implode = implode("','", $explode); //vulve a String y agrega comillas
                if (Dh03::existeCategoriaDiferencial($legajos[$i]['nro_legaj'], $implode)) {
                    $legajos[$i]['IMPORTE_IMPON'] = 0;
                }

                $legajos[$i]['ImporteImponibleSinSAC'] = $legajos[$i]['IMPORTE_IMPON'] - $legajos[$i]['ImporteSACNoDocente'];


                $tope_jubil_personal = $TopeJubilatorioPersonal;
                if ($legajos[$i]['ImporteSAC'] > 0)
                    $tope_jubil_personal = $TopeJubilatorioPersonal + $TopeSACJubilatorioPers;
                else
                    $tope_jubil_personal = $TopeJubilatorioPersonal;

                if ($legajos[$i]['ImporteImponibleSinSAC']  > $tope_jubil_personal) {
                    if ($trunca_tope == 1) {
                        $legajos[$i]['DiferenciaImponibleConTope'] = $legajos[$i]['ImporteImponibleSinSAC'] - $TopeJubilatorioPersonal;
                        $legajos[$i]['IMPORTE_IMPON']             -= $legajos[$i]['DiferenciaImponibleConTope'];
                    }
                }


                $otra_actividad = $this->sicossCalculoRepository->otraActividad($legajo);
                $legajos[$i]['ImporteBrutoOtraActividad']  = $otra_actividad['importebrutootraactividad'];
                $legajos[$i]['ImporteSACOtraActividad']    = $otra_actividad['importesacotraactividad'];

                if (($legajos[$i]['ImporteBrutoOtraActividad'] != 0) || ($legajos[$i]['ImporteSACOtraActividad'] != 0)) {
                    if (($legajos[$i]['ImporteBrutoOtraActividad'] + $legajos[$i]['ImporteSACOtraActividad'])  >=  ($TopeSACJubilatorioPers + $TopeJubilatorioPatronal)) {
                        $legajos[$i]['IMPORTE_IMPON'] = 0.00;
                    } else {
                        $imp_1_tope = 0.0;
                        $imp_1_tope_sac = 0.0;

                        if ($TopeJubilatorioPersonal > $legajos[$i]['ImporteBrutoOtraActividad']) {
                            $imp_1_tope += $TopeJubilatorioPersonal - $legajos[$i]['ImporteBrutoOtraActividad'];
                        }

                        if ($TopeSACJubilatorioPers > $legajos[$i]['ImporteSACOtraActividad']) {
                            $imp_1_tope_sac += $TopeSACJubilatorioPers - $legajos[$i]['ImporteSACOtraActividad'];
                        }

                        if ($imp_1_tope > $legajos[$i]['ImporteImponibleSinSAC']) {
                            $imp_1_tope = $legajos[$i]['ImporteImponibleSinSAC'];
                        }

                        if ($imp_1_tope_sac > $legajos[$i]['ImporteSACPatronal']) {
                            $imp_1_tope_sac = $legajos[$i]['ImporteSACPatronal'];
                        }

                        $legajos[$i]['IMPORTE_IMPON'] = $imp_1_tope_sac + $imp_1_tope;
                    }
                }

                $legajos[$i]['DifSACImponibleConOtroTope']   = 0;
                $legajos[$i]['DifImponibleConOtroTope']      = 0;
                if ($legajos[$i]['ImporteSACOtroAporte'] > $TopeSACJubilatorioOtroAp) {
                    if ($trunca_tope == 1) {
                        $legajos[$i]['DifSACImponibleConOtroTope'] = $legajos[$i]['ImporteSACOtroAporte'] - $TopeSACJubilatorioOtroAp;
                        $legajos[$i]['ImporteImponible_4']        -= $legajos[$i]['DifSACImponibleConOtroTope'];
                        $legajos[$i]['ImporteSACOtroAporte']       = $TopeSACJubilatorioOtroAp;
                    }
                }
                $legajos[$i]['OtroImporteImponibleSinSAC'] = $legajos[$i]['ImporteImponible_4'] - $legajos[$i]['ImporteSACOtroAporte'];
                if ($legajos[$i]['OtroImporteImponibleSinSAC'] > $TopeOtrosAportesPersonales) {
                    if ($trunca_tope == 1) {
                        $legajos[$i]['DifImponibleConOtroTope'] = $legajos[$i]['OtroImporteImponibleSinSAC'] - $TopeOtrosAportesPersonales;
                        $legajos[$i]['ImporteImponible_4']     -= $legajos[$i]['DifImponibleConOtroTope'];
                    }
                }
                if ($legajos[$i]['ImporteImponible_6'] != 0 && $legajos[$i]['TipoDeOperacion'] == 1) {
                    $legajos[$i]['IMPORTE_IMPON'] = 0;
                }
                // Calcular Sueldo m�s Adicionales
                $legajos[$i]['ImporteSueldoMasAdicionales'] = $legajos[$i]['ImporteImponiblePatronal'] -
                    $legajos[$i]['ImporteSAC'] -
                    $legajos[$i]['ImporteHorasExtras'] -
                    $legajos[$i]['ImporteZonaDesfavorable'] -
                    $legajos[$i]['ImporteVacaciones'] -
                    $legajos[$i]['ImportePremios'] -
                    $legajos[$i]['ImporteAdicionales'];
                if ($legajos[$i]['ImporteSueldoMasAdicionales'] > 0) {
                    $legajos[$i]['ImporteSueldoMasAdicionales'] -= $legajos[$i]['IncrementoSolidario'];
                }

                if (is_null($legajos[$i]['trabajadorconvencionado'])) {
                    $legajos[$i]['trabajadorconvencionado'] = self::$trabajadorConvencionado;
                }

                // Sumariza las asiganciones familiares en el bruto y deja las asiganciones familiares en cero, esto si en configuracion esta chequeado
                if (self::$asignacion_familiar) {
                    $legajos[$i]['IMPORTE_BRUTO'] += $legajos[$i]['AsignacionesFliaresPagadas'];
                    $legajos[$i]['AsignacionesFliaresPagadas'] = 0;
                }

                // Por ticket #3947. Check "Generar ART con tope"
                if (MapucheConfig::getParametroRrhh('Sicoss', 'ARTconTope', '1') === '0') // Sin tope
                {
                    $legajos[$i]['importeimponible_9'] = $legajos[$i]['Remuner78805'];
                } else // Con tope
                {
                    $legajos[$i]['importeimponible_9'] = $legajos[$i]['ImporteImponible_4'];
                }

                // Por ticket #3947. Check "Considerar conceptos no remunerativos en c�lculo de ART?"
                if (MapucheConfig::getParametroRrhh('Sicoss', 'ConceptosNoRemuEnART', '0') === '1') // Considerar conceptos no remunerativos
                {
                    $legajos[$i]['importeimponible_9'] += $legajos[$i]['ImporteNoRemun'];
                }

                // por GDS #5913 Incorporaci�n de conceptos no remunerativos a las remuneraciones 4 y 8 de SICOSS
                $legajos[$i]['Remuner78805'] += $legajos[$i]['NoRemun4y8'];
                $legajos[$i]['ImporteImponible_5'] = $legajos[$i]['ImporteImponible_4'];
                $legajos[$i]['ImporteImponible_4'] += $legajos[$i]['NoRemun4y8'];
                $legajos[$i]['ImporteImponible_4'] += $legajos[$i]['ImporteTipo91'];

                $legajos[$i]['IMPORTE_BRUTO'] += $legajos[$i]['ImporteNoRemun96'];
                $total['bruto']       += round($legajos[$i]['IMPORTE_BRUTO'], 2);
                $total['imponible_1'] += round($legajos[$i]['IMPORTE_IMPON'], 2);
                $total['imponible_2'] += round($legajos[$i]['ImporteImponiblePatronal'], 2);
                $total['imponible_4'] += round($legajos[$i]['ImporteImponible_4'], 2);
                $total['imponible_5'] += round($legajos[$i]['ImporteImponible_5'], 2);
                $total['imponible_8'] += round($legajos[$i]['Remuner78805'], 2);
                $total['imponible_6'] += round($legajos[$i]['ImporteImponible_6'], 2);
                $total['imponible_9'] += round($legajos[$i]['importeimponible_9'], 2);

                $legajos_validos[$j] = $legajos[$i];
                $j++;
            } // fin else que verifica que los importes sean distintos de 0
            // Si los importes son cero el legajo no se agrega al archivo sicoss; pero cuando tengo el check de licencias por interface y ademas el legajo tiene licencias entonces si va
            elseif ($datos['check_lic'] && ($legajos[$i]['licencia'] == 1)) {
                // Inicializo variables faltantes en cero
                $legajos[$i]['ImporteSueldoMasAdicionales'] = 0;
                if (is_null($legajos[$i]['trabajadorconvencionado'])) {
                    $legajos[$i]['trabajadorconvencionado'] = self::$trabajadorConvencionado;
                }

                if ($datos['seguro_vida_patronal'] == 1 && $datos['check_lic'] == 1) {
                    $legajos[$i]['SeguroVidaObligatorio'] = 1;
                }
                $legajos_validos[$j] = $legajos[$i];
                $j++;
            } elseif ($check_sin_activo && $legajos[$i]['codigosituacion'] == 14) {
                $legajos_validos[$j] = $legajos[$i];
                $j++;
            }
        }

        if (!empty($legajos_validos)) {
            if ($retornar_datos === TRUE)
                return $legajos_validos;
            self::grabarEnTxt($legajos_validos, $nombre_arch);
        }


        return $total;
    }


    // Dado un arreglo, doy formato y agrego a archivo
    public static function grabarEnTxt($legajos, $nombre_arch)
    {
        //Para todos los datos obtenidos habra q calcular lo que no esta en la consulta
        $archivo = storage_path('app/comunicacion/sicoss/' . $nombre_arch . '.txt');
        $fh = fopen($archivo, 'w') or die("Error!!");
        // Proceso la tabla, le agrego las longitudes correpondientes
        for ($i = 0; $i < count($legajos); $i++) {
            fwrite(
                $fh,
                $legajos[$i]['cuit'] .                                                                // Campo 1
                    self::llena_blancos_mod($legajos[$i]['apyno'], 30) .                                             // Campo 2
                    $legajos[$i]['conyugue'] .                                                                        // Campo 3
                    self::llena_importes($legajos[$i]['hijos'], 2) .                                                 // Campo 4
                    self::llena_importes($legajos[$i]['codigosituacion'], 2) .                                       // Campo 5 TODO: Preguntar �es el que viene de dha8?
                    self::llena_importes($legajos[$i]['codigocondicion'], 2) .                                       // Campo 6
                    self::llena_importes($legajos[$i]['TipoDeActividad'], 3) .                                       // Campo 7 - Segun prioridad es codigoactividad de dha8 u otro valor, ver funcion sumarizar_conceptos_por_tipos_grupos
                    self::llena_importes($legajos[$i]['codigozona'], 2) .                                            // Campo 8
                    self::llena_blancos_izq(number_format($legajos[$i]['aporteadicional'] ?? 0.0, 2, ',', ''), 5) .            // Campo 9 - Porcentaje de Aporte Adicional Obra Social
                    self::llena_importes($legajos[$i]['codigocontratacion'], 3) .                                    // Campo 10
                    self::llena_importes($legajos[$i]['codigo_os'], 6) .
                    self::llena_importes($legajos[$i]['adherentes'], 2) .                                            // Campo 12 - Seg�n este chequeado en configuraci�n informo 0 o uno (sumarizar_conceptos_por_tipos_grupos) o cantidad de adherentes (dh09)
                    self::llena_blancos_izq(number_format($legajos[$i]['IMPORTE_BRUTO'] ?? 0.0, 2, ',', ''), 12) .             // Campo 13
                    self::llena_blancos_izq(number_format($legajos[$i]['IMPORTE_IMPON'] ?? 0.0, 2, ',', ''), 12) .             // Campo 14
                    self::llena_blancos_izq(number_format($legajos[$i]['AsignacionesFliaresPagadas'] ?? 0.0, 2, ',', ''), 9) . // Campo 15
                    self::llena_blancos_izq(number_format($legajos[$i]['IMPORTE_VOLUN'] ?? 0.0, 2, ',', ''), 9) .              // Campo 16
                    self::llena_blancos_izq(number_format($legajos[$i]['IMPORTE_ADICI'] ?? 0.0, 2, ',', ''), 9) .              // Campo 17
                    self::llena_blancos_izq(number_format(abs($legajos[$i]['ImporteSICOSSDec56119'] ?? 0.0), 2, ',', ''), 9) .      //exedAportesSS
                    '     0,00' .                                                                                     //exedAportesOS
                    self::llena_blancos($legajos[$i]['provincialocalidad'], 50) .                                    //Campo 20
                    self::llena_blancos_izq(number_format($legajos[$i]['ImporteImponiblePatronal'] ?? 0.0, 2, ',', ''), 12) .  // Campo 21 - Imponible 2
                    self::llena_blancos_izq(number_format($legajos[$i]['ImporteImponiblePatronal'] ?? 0.0, 2, ',', ''), 12) .  // Campo 22 - Imponible 3
                    self::llena_blancos_izq(number_format($legajos[$i]['ImporteImponible_4'] ?? 0.0, 2, ',', ''), 12) .        // Campo 23 - Imponible 4
                    '00' .                                                                                            // campo 24 - codigo siniestrado
                    '0' .                                                                                             // Campo 25 - marca de corresponde reduccion
                    '000000,00' .                                                                                     // Campo 26 -  capital de recomposicion
                    self::$tipoEmpresa .
                    self::llena_blancos_izq(number_format($legajos[$i]['AporteAdicionalObraSocial'] ?? 0.0, 2, ',', ''), 9) .                                                                                     // Campo 28 - aporte adicional obra social
                    $legajos[$i]['regimen'] .
                    self::llena_importes($legajos[$i]['codigorevista1'], 2) .                                       // campo 30 - codigo de revista 1 se informa igual que codigosituacion
                    self::llena_importes($legajos[$i]['fecharevista1'], 2) .                                        // campo 31 - Dia inicio Situaci�n de Revista 1
                    self::llena_importes($legajos[$i]['codigorevista2'], 2) .                                       // Situaci�n de Revista 2
                    self::llena_importes($legajos[$i]['fecharevista2'], 2) .                                        // Dia inicio Situaci�n de Revista 2
                    self::llena_importes($legajos[$i]['codigorevista3'], 2) .                                       // Situaci�n de Revista 3
                    self::llena_importes($legajos[$i]['fecharevista3'], 2) .                                        // Dia inicio Situaci�n de Revista 3
                    self::llena_blancos_izq(number_format($legajos[$i]['ImporteSueldoMasAdicionales'] ?? 0.0, 2, ',', ''), 12) .        // Campo 36
                    self::llena_blancos_izq(number_format($legajos[$i]['ImporteSAC'] ?? 0.0, 2, ',', ''), 12) .                // Campo 37
                    self::llena_blancos_izq(number_format($legajos[$i]['ImporteHorasExtras'] ?? 0.0, 2, ',', ''), 12) .        // Campo 38
                    self::llena_blancos_izq(number_format($legajos[$i]['ImporteZonaDesfavorable'] ?? 0.0, 2, ',', ''), 12) .   // Campo 39
                    self::llena_blancos_izq(number_format($legajos[$i]['ImporteVacaciones'] ?? 0.0, 2, ',', ''), 12) .         // Campo 40
                    '0000000' . self::llena_importes($legajos[$i]['dias_trabajados'], 2) .                            // Campo 41 - D�as trabajados
                    self::llena_blancos_izq(number_format($legajos[$i]['ImporteImponible_4'] - $legajos[$i]['ImporteTipo91'], 2, ',', ''), 12) .        // Campo 42 - Imponible5 = Imponible4 - ImporteTipo91
                    $legajos[$i]['trabajadorconvencionado'] .
                    self::llena_blancos_izq(number_format($legajos[$i]['ImporteImponible_6'] ?? 0.0, 2, ',', ''), 12) .        // Campo 44 - Imponible 6
                    $legajos[$i]['TipoDeOperacion'] .                                                                 // Campo 45 - Segun se redondee o no importe imponible 6
                    self::llena_blancos_izq(number_format($legajos[$i]['ImporteAdicionales'] ?? 0.0, 2, ',', ''), 12) .        // Campo 46
                    self::llena_blancos_izq(number_format($legajos[$i]['ImportePremios'] ?? 0.0, 2, ',', ''), 12) .            // Campo 47
                    self::llena_blancos_izq(number_format($legajos[$i]['Remuner78805'] ?? 0.0, 2, ',', ''), 12) .              // Campo 48
                    self::llena_blancos_izq(number_format($legajos[$i]['ImporteImponible_6'] ?? 0.0, 2, ',', ''), 12) .        // Campo 49 - Imponible7 = Imponible6
                    //redondeo las HS extras, si vienen por ejemplo 10.5 en sicoss informo 11. Esto es porque el
                    //campo de sicoss es de 3 caracteres y los 10.5 los informaria como 0.5
                    self::llena_importes(ceil($legajos[$i]['CantidadHorasExtras']), 3) .                                   // Campo 50
                    self::llena_blancos_izq(number_format($legajos[$i]['ImporteNoRemun'] ?? 0.0, 2, ',', ''), 12) .            // Campo 51
                    self::llena_blancos_izq(number_format($legajos[$i]['ImporteMaternidad'] ?? 0.0, 2, ',', ''), 12) .         // Campo 52
                    self::llena_blancos_izq(number_format($legajos[$i]['ImporteRectificacionRemun'] ?? 0.0, 2, ',', ''), 9) .  // Campo 53
                    self::llena_blancos_izq(number_format($legajos[$i]['importeimponible_9'] ?? 0.0, 2, ',', ''), 12) .        // Campo 54 = Imponible8 (Campo 48) + Conceptos No remunerativos (Campo 51)
                    self::llena_blancos_izq(number_format($legajos[$i]['ContribTareaDif'] ?? 0.0, 2, ',', ''), 9) .            // Campo 55 - Contribuci�n Tarea Diferencial
                    '000' .                                                                                             // Campo 56 - Horas Trabajadas
                    $legajos[$i]['SeguroVidaObligatorio'] .                                                           // Campo 57 - Seguro  de Vida Obligatorio
                    self::llena_blancos_izq(number_format($legajos[$i]['ImporteSICOSS27430'] ?? 0.0, 2, ',', ''), 12) .         // Campo 58 - Importe a detraer Ley 27430
                    self::llena_blancos_izq(number_format($legajos[$i]['IncrementoSolidario'] ?? 0.0, 2, ',', ''), 12) . // Campo 59 - Incremento Solidario para empresas del sector privado y p�blico (D. 14/2020 y 56/2020)
                    self::llena_blancos_izq(number_format(0, 2, ',', ''), 12) .                                          // Campo 60 - Remuneraci�n 11
                    "\r\n"
            );
        }
        fclose($fh);
    }

    // Similar a VerificarConceptosRemuneratorios en pampa.
    // Dado un legajo hace la sumarizaci�n de conceptos liquidados seg�n corresponda:
    // por tipo de grupo al que pertenece un concepto o codigo de concepto
    public function sumarizar_conceptos_por_tipos_grupos($nro_leg, &$leg)
    {
        $leg['ImporteSAC']                = 0;
        $leg['SACPorCargo']               = 0;
        $leg['ImporteHorasExtras']        = 0;
        $leg['ImporteVacaciones']         = 0;
        $leg['ImporteRectificacionRemun'] = 0;
        $leg['ImporteAdicionales']        = 0;
        $leg['ImportePremios']            = 0;
        $leg['ImporteNoRemun']            = 0;
        $leg['ImporteMaternidad']         = 0;
        $leg['ImporteZonaDesfavorable']   = 0;
        $leg['PrioridadTipoDeActividad']  = 0;
        $leg['IMPORTE_VOLUN']             = 0;
        $leg['IMPORTE_ADICI']             = 0;
        $leg['TipoDeActividad']           = 0;
        $leg['ImporteImponible_6']        = 0;
        $leg['SACInvestigador']           = 0;
        $leg['CantidadHorasExtras']       = 0;
        $leg['SeguroVidaObligatorio']     = 0;
        $leg['ImporteImponibleBecario']   = 0;
        $leg['AporteAdicionalObraSocial']   = 0;
        $leg['ImporteSICOSS27430'] = 0;
        $leg['ImporteSICOSSDec56119'] = 0;
        $leg['ImporteSACDoce']  = 0;
        $leg['ImporteSACAuto']  = 0;
        $leg['ImporteSACNodo']  = 0;
        $leg['ContribTareaDif']  = 0;
        $leg['NoRemun4y8']  = 0;
        $leg['IncrementoSolidario'] = 0;
        $leg['ImporteNoRemun96'] = 0;
        $leg['ImporteTipo91'] = 0;


        $informar_becarios                = MapucheConfig::getSicossInformarBecarios();

        // Voy a guardar en esta variable los numeros de cargos que son investigador
        $cargoInvestigador                = [];
        // En el caso de que en check 'Toma en cuenta Familiares a Cargo para informar SICOSS?' en configuraci�n -> impositivos -> parametros sicoss sea false
        // voy a fijarme si se liquido un concepto igual al configurado como obra social familiar a cargo. Informo 0 o 1 (no se liquido o se liquido algun concepto igual al definido)
        if (self::$cantidad_adherentes_sicoss == 0)
            $leg['adherentes'] = 0;

        $conceptos_liq_por_leg = $this->consultar_conceptos_liquidados($nro_leg, 'true');

        // Sumarizo donde corresponda para cada concepto liquidado
        // Cuando recorro guardo el numero de cargo si es investigador, para luego procesar en calcularSACInvestigador
        $conce_hs_extr = array();
        $cont = 0;
        for ($i = 0; $i < count($conceptos_liq_por_leg); $i++) {
            $importe            = $conceptos_liq_por_leg[$i]['impp_conce'];
            $importe_novedad    = $conceptos_liq_por_leg[$i]['nov1_conce'];
            $grupos_concepto    = $conceptos_liq_por_leg[$i]['tipos_grupos'];
            $codn_concepto      = $conceptos_liq_por_leg[$i]['codn_conce'];
            $nro_cargo          = $conceptos_liq_por_leg[$i]['nro_cargo'];
            $codigo_obra_social = $leg['codigo_os'];


            if (preg_match('/[^\d]+6[^\d]+/', $grupos_concepto)) {
                $leg['ImporteHorasExtras'] += $importe;
                // Si tiene el check de sumar horas extras por novedad ademas sumo en horas extras novedad1
                if (self::$hs_extras_por_novedad == 1) {
                    $horas = $this->sicossCalculoRepository->calculoHorasExtras($codn_concepto, $nro_cargo);
                    //verifico que las hs extras para el concepto determinado no se hayan sumado para sumarlas e informarlas en sicoss
                    if (!in_array($codn_concepto, $conce_hs_extr)) {
                        $conce_hs_extr[] = $codn_concepto;
                        $leg['CantidadHorasExtras'] += $horas['sum_nov1'];
                    }
                }
            }

            if (preg_match('/[^\d]+7[^\d]+/', $grupos_concepto))
                $leg['ImporteZonaDesfavorable'] += $importe;

            if (preg_match('/[^\d]+8[^\d]+/', $grupos_concepto))
                $leg['ImporteVacaciones'] += $importe;

            if (preg_match('/[^\d]+9[^\d]+/', $grupos_concepto)) {
                $leg['ImporteSAC']  += $importe;

                if ($conceptos_liq_por_leg[$i]['codigoescalafon'] == 'NODO')
                    $leg['ImporteSACNodo'] += $importe;
                if ($conceptos_liq_por_leg[$i]['codigoescalafon'] == 'AUTO')
                    $leg['ImporteSACAuto'] += $importe;
                if ($conceptos_liq_por_leg[$i]['codigoescalafon'] == 'DOCE')
                    $leg['ImporteSACDoce'] += $importe;
            }

            if (preg_match('/[^\d]+11[^\d]+/', $grupos_concepto)) {
                $leg['ImporteImponible_6'] += $importe;
                if ($leg['PrioridadTipoDeActividad'] < 38)
                    $leg['PrioridadTipoDeActividad'] = 38;
                if (($leg['PrioridadTipoDeActividad'] == 87) || ($leg['PrioridadTipoDeActividad'] == 88))
                    $leg['PrioridadTipoDeActividad'] = 38;
                array_push($cargoInvestigador, $nro_cargo);
            }

            if (preg_match('/[^\d]+12[^\d]+/', $grupos_concepto)) {
                $leg['ImporteImponible_6'] += $importe;
                if ($leg['PrioridadTipoDeActividad'] < 34)
                    $leg['PrioridadTipoDeActividad'] = 34;
                array_push($cargoInvestigador, $nro_cargo);
            }

            if (preg_match('/[^\d]+13[^\d]+/', $grupos_concepto)) {
                $leg['ImporteImponible_6'] += $importe;
                if ($leg['PrioridadTipoDeActividad'] < 35)
                    $leg['PrioridadTipoDeActividad'] = 35;
                array_push($cargoInvestigador, $nro_cargo);
            }

            if (preg_match('/[^\d]+14[^\d]+/', $grupos_concepto)) {
                $leg['ImporteImponible_6'] += $importe;
                if ($leg['PrioridadTipoDeActividad'] < 36)
                    $leg['PrioridadTipoDeActividad'] = 36;
                if ($leg['PrioridadTipoDeActividad'] == 87 || $leg['PrioridadTipoDeActividad'] == 88)
                    $leg['PrioridadTipoDeActividad'] = 36;
                array_push($cargoInvestigador, $nro_cargo);
            }

            if (preg_match('/[^\d]+15[^\d]+/', $grupos_concepto)) {
                $leg['ImporteImponible_6'] += $importe;
                if ($leg['PrioridadTipoDeActividad'] < 37)
                    $leg['PrioridadTipoDeActividad'] = 37;
                if ($leg['PrioridadTipoDeActividad'] == 87 || $leg['PrioridadTipoDeActividad'] == 88)
                    $leg['PrioridadTipoDeActividad'] = 37;
                array_push($cargoInvestigador, $nro_cargo);
            }

            if (preg_match('/[^\d]+16[^\d]+/', $grupos_concepto)) {
                $leg['AporteAdicionalObraSocial'] += $importe;
            }

            if (preg_match('/[^\d]+21[^\d]+/', $grupos_concepto))
                $leg['ImporteAdicionales'] += $importe;

            if (preg_match('/[^\d]+22[^\d]+/', $grupos_concepto))
                $leg['ImportePremios'] += $importe;

            // conceptos no remunerativos
            if (preg_match('/[^\d]+45[^\d]+/', $grupos_concepto))
                $leg['ImporteNoRemun']  += $importe;

            if (preg_match('/[^\d]+46[^\d]+/', $grupos_concepto))
                $leg['ImporteRectificacionRemun'] += $importe;

            if (preg_match('/[^\d]+47[^\d]+/', $grupos_concepto))
                $leg['ImporteMaternidad'] += $importe;

            if (preg_match('/[^\d]+48[^\d]+/', $grupos_concepto)) {
                $leg['ImporteImponible_6'] += $importe;
                if ($leg['PrioridadTipoDeActividad'] < 36 || $leg['PrioridadTipoDeActividad'] == 88)
                    $leg['PrioridadTipoDeActividad'] = 87;
                array_push($cargoInvestigador, $nro_cargo);
            }

            if (preg_match('/[^\d]+49[^\d]+/', $grupos_concepto)) {
                $leg['ImporteImponible_6'] += $importe;
                if ($leg['PrioridadTipoDeActividad'] < 36)
                    $leg['PrioridadTipoDeActividad'] = 88;
                array_push($cargoInvestigador, $nro_cargo);
            }
            if (preg_match('/[^\d]+58[^\d]+/', $grupos_concepto))
                $leg['SeguroVidaObligatorio'] = 1;

            if (self::$codigo_os_aporte_adicional == $codn_concepto)
                $leg['IMPORTE_ADICI'] += $importe;

            if (self::$aportes_voluntarios == $codn_concepto)
                $leg['IMPORTE_VOLUN'] += $importe;

            if (self::$cantidad_adherentes_sicoss == 0 && self::$codigo_obrasocial_fc == $codn_concepto)
                $leg['adherentes'] = 1;

            if (preg_match('/[^\d]+24[^\d]+/', $grupos_concepto) && self::$hs_extras_por_novedad == 0) {
                $leg['CantidadHorasExtras'] += $importe;
            }

            if (preg_match('/[^\d]+67[^\d]+/', $grupos_concepto) && $informar_becarios == 1) {
                $leg['ImporteImponibleBecario'] += $importe;
            }

            if (preg_match('/[^\d]+81[^\d]+/', $grupos_concepto)) {
                $leg['ImporteSICOSS27430'] += $importe;
            }
            if (preg_match('/[^\d]+83[^\d]+/', $grupos_concepto)) {
                $leg['ImporteSICOSSDec56119'] += $importe;
            }

            if (preg_match('/[^\d]+84[^\d]+/', $grupos_concepto)) {
                $leg['NoRemun4y8'] += $importe;
            }

            /*if(preg_match('/[^\d]+85[^\d]+/', $grupos_concepto))
                {
                    $leg['ContribTareaDif'] += $importe;

                }*/

            // #6204 Nuevos campos SICOSS "Incremento Salarial" y "Remuneraci�n 11"
            if (preg_match('/[^\d]+86[^\d]+/', $grupos_concepto)) {
                $leg['IncrementoSolidario'] += $importe;
            }

            // Tipo 91- AFIP Base de C�lculo Diferencial Aportes OS y FSR
            if (preg_match('/[^\d]+91[^\d]+/', $grupos_concepto)) {
                $leg['ImporteTipo91'] += $importe;
            }

            // nuevo tipo de grupo 96, conceptos NoRemun que solo impacten en la Remuneraci�n bruta total
            if (preg_match('/[^\d]+96[^\d]+/', $grupos_concepto))
                $leg['ImporteNoRemun96']  += $importe;
        }
        // Segun prioridad selecciono el valor de dha8 o no; se informa TipoDeActividad como codigo de actividad
        if ($leg['PrioridadTipoDeActividad'] == 38 || $leg['PrioridadTipoDeActividad'] == 0)
            $leg['TipoDeActividad'] = $leg['codigoactividad'];
        elseif (($leg['PrioridadTipoDeActividad'] >= 34 && $leg['PrioridadTipoDeActividad'] <= 37) ||
            $leg['PrioridadTipoDeActividad'] == 87 || $leg['PrioridadTipoDeActividad'] == 88
        )
            $leg['TipoDeActividad'] = $leg['PrioridadTipoDeActividad'];

        $leg['SACInvestigador'] = $this->calcularSACInvestigador($nro_leg, $cargoInvestigador);
    }

    // Recibo un arreglo con los cargos que son investigadores, para cada uno calculo el sac investigador y sumarizo. Se tiene en cuenta los de tipo 9
    public function calcularSACInvestigador($nro_leg, $cargos)
    {
        $sacInvestigador = 0;
        $cargos = array_unique($cargos); // limpio cargos duplicados
        foreach ($cargos as $cargo) {
            $where = " nro_cargo = $cargo
                           --filtro solo los que tienen tipo de concepto = 9 como es una lista uso exp. reg.
                           AND array_to_string(tipos_grupos,',') ~ '(:?^|,)+9(:?$|,)'";
            $conceptos_liq_por_leg = $this->consultar_conceptos_liquidados($nro_leg, $where);
            for ($j = 0; $j < count($conceptos_liq_por_leg); $j++) {
                $sacInvestigador += $conceptos_liq_por_leg[$j]['impp_conce'];
            }
        }

        return $sacInvestigador;
    }


    // Obtengo los conceptos liquidados para el legajo, su importe y los tipos de grupo asociados al concepto
    public function consultar_conceptos_liquidados($nro_leg, $where)
    {
        $sql_conceptos_fltrados =
            "
                                            SELECT
                                                    impp_conce,
                                                    nov1_conce,
                                                       codn_conce,
                                                       tipos_grupos,
                                                       nro_cargo,
                                                       codigoescalafon
                                            FROM
                                                    conceptos_liquidados
                                            WHERE
                                                   nro_legaj = $nro_leg
                                                   AND tipos_grupos IS NOT NULL
                                                   AND $where
                                        ";

        $conceptos_filtrados = DB::connection($this->getConnectionName())->select($sql_conceptos_fltrados);

        return $conceptos_filtrados;
    }



    // --- Funciones auxiliares para dar formato a campos ---

    static function llena_importes($valor, $longitud)
    {
        if ($valor === null) {
            $valor = '';
        }
        if (strlen(trim($valor)) > $longitud) {
            return substr($valor, - ($longitud));
        } else {
            return str_pad($valor, $longitud, "0", STR_PAD_LEFT);
        }
    }

    static function llena_blancos_izq($texto, $longitud)
    {
        if (strlen(trim($texto)) > $longitud) {
            return substr($texto, - ($longitud));
        } else {
            return str_pad($texto, $longitud, " ", STR_PAD_LEFT);
        }
    }

    // En los casos que se supera la longitud maxima con llena_blancos_izq se cortaban las iniciales en los agentes
    static function llena_blancos_mod($texto, $longitud)
    {
        if (strlen(trim($texto)) > $longitud) {
            return substr($texto, 0, ($longitud));
        } else {
            return str_pad($texto, $longitud, " ", STR_PAD_RIGHT);
        }
    }

    static function llena_blancos($texto, $longitud)
    {
        if (strlen(trim($texto)) > $longitud) {
            return substr($texto, - ($longitud));
        } else {
            return str_pad($texto, $longitud, " ", STR_PAD_RIGHT);
        }
    }

    static function transformar_a_recordset($totales_periodo)
    {
        // Devuelvo importes totales con formato adecuado para un cuadro toba
        $totales = [];
        $i = 0;
        foreach ($totales_periodo as $clave => $valor) {
            $totales[$i++] = array('variable' => 'BRUTO',        'valor' => $valor['bruto'], 'periodo' => $clave);
            $totales[$i++] = array('variable' => 'IMPONIBLE 1',    'valor' => $valor['imponible_1'], 'periodo' => $clave);
            $totales[$i++] = array('variable' => 'IMPONIBLE 2',    'valor' => $valor['imponible_2'], 'periodo' => $clave);
            $totales[$i++] = array('variable' => 'IMPONIBLE 3',    'valor' => $valor['imponible_2'], 'periodo' => $clave);
            $totales[$i++] = array('variable' => 'IMPONIBLE 4',    'valor' => $valor['imponible_4'], 'periodo' => $clave);
            $totales[$i++] = array('variable' => 'IMPONIBLE 5',    'valor' => $valor['imponible_5'], 'periodo' => $clave);
            $totales[$i++] = array('variable' => 'IMPONIBLE 6',    'valor' => $valor['imponible_6'], 'periodo' => $clave);
            $totales[$i++] = array('variable' => 'IMPONIBLE 7',    'valor' => $valor['imponible_6'], 'periodo' => $clave);
            $totales[$i++] = array('variable' => 'IMPONIBLE 8',    'valor' => $valor['imponible_8'], 'periodo' => $clave);
            $totales[$i++] = array('variable' => 'IMPONIBLE 9',    'valor' => $valor['imponible_9'], 'periodo' => $clave);
        }
        return $totales;
    }

    private static function getStaticConnectionName()
    {
        $instance = new static(
            app(LicenciaRepositoryInterface::class),
            app(Dh03RepositoryInterface::class),
            app(Dh21RepositoryInterface::class),
            app(Dh01RepositoryInterface::class),
            app(SicossCalculoRepositoryInterface::class),
            app(SicossEstadoRepositoryInterface::class)
        );
        return $instance->getConnectionName();
    }
}
