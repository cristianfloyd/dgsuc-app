<?php

use App\Models\Dh01;
use App\Models\Dh03;
use App\Models\Dh11;
use Illuminate\Support\Facades\DB;
use App\Models\Mapuche\MapucheConfig;
use App\Traits\MapucheConnectionTrait;
use App\Services\Mapuche\LicenciaService;
use App\Repositories\Sicoss\Dh03Repository;

class Sicoss
{
    use mapucheConnectionTrait;
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

    // public function __construct(
    //     protected LicenciaService $licenciaService
    // ) {}



    public static function genera_sicoss(
        $datos,
        $testeo_directorio_salida = '',
        $testeo_prefijo_archivos = '',
        $retornar_datos = FALSE
    ) {
        // Se necesita filtrar datos del periodo vigente
        $periodo       = MapucheConfig::getPeriodoCorriente();
        $per_mesct     = $periodo['per_mesct'];
        $per_anoct     = $periodo['per_anoct'];

        // Seteo valores de rrhhini
        self::$codigo_obra_social_default = self::quote(MapucheConfig::getDefaultsObraSocial());
        self::$aportes_voluntarios        = MapucheConfig::getTopesJubilacionVoluntario();
        self::$codigo_os_aporte_adicional = MapucheConfig::getConceptosObraSocialAporteAdicional();
        self::$codigo_obrasocial_fc       = MapucheConfig::getConceptosObraSocialFliarAdherente();                   // concepto seteado en rrhhini bajo el cual se liquida el familiar a cargo
        self::$tipoEmpresa                = MapucheConfig::getDatosUniversidadTipoEmpresa();
        self::$cantidad_adherentes_sicoss = MapucheConfig::getConceptosInformarAdherentesSicoss();                   // Seg�n sea cero o uno informa datos de dh09 o se fija si existe un cpncepto liquidado bajo el concepto de codigo_obrasocial_fc
        self::$asignacion_familiar        = MapucheConfig::getConceptosAcumularAsigFamiliar();                 // Si es uno se acumulan las asiganciones familiares en Asignacion Familiar en Remuneraci�n Total (importe Bruto no imponible)
        self::$trabajadorConvencionado    = MapucheConfig::getDatosUniversidadTrabajadorConvencionado();
        self::$codc_reparto                     = self::quote(MapucheConfig::getDatosCodcReparto());
        self::$porc_aporte_adicional_jubilacion = MapucheConfig::getPorcentajeAporteDiferencialJubilacion();
        self::$hs_extras_por_novedad        = MapucheConfig::getSicossHorasExtrasNovedades();   // Lee el valor HorasExtrasNovedades de RHHINI que determina si es verdadero se suman los valores de las novedades y no el importe.
        self::$categoria_diferencial        = MapucheConfig::getCategoriasDiferencial(); //obtengo las categorias seleccionadas en configuracion
        self::$codc_reparto                 = self::quote(MapucheConfig::getDatosCodcReparto());
        $where = ' true ';

        $opcion_retro  = $datos['check_retro'];
        $filtro_legajo = isset($datos['nro_legaj']) ? $datos['nro_legaj'] : '';
        $path = storage_path('app/comunicacion/sicoss/');


        // Si no filtro por n�mero de legajo => obtengo todos los legajos
        if (!empty($filtro_legajo))
            $where = 'dh01.nro_legaj= ' . $filtro_legajo . ' ';

        $where_periodo = ' true ';

        //si se envia nro_liqui desde la generacion de libro de sueldo
        if (isset($datos['nro_liqui'])) {
            $where_liqui = $where . ' AND dh21.nro_liqui = ' . self::quote($datos['nro_liqui']);
            self::obtener_conceptos_liquidados($per_anoct, $per_mesct, $where_liqui);
        } else {
            self::obtener_conceptos_liquidados($per_anoct, $per_mesct, $where);
        }

        self::$archivos = [];
        $totales = [];

        $licencias_agentes_no_remunem = self::get_licencias_vigentes($where);
        $licencias_agentes_remunem = self::get_licencias_protecintegral_vacaciones($where);
        $licencias_agentes = array_merge($licencias_agentes_no_remunem, $licencias_agentes_remunem);

        // Si no tengo tildado el check el proceso genera un unico archivo sin tener en cuenta a�o y mes retro
        if ($opcion_retro == 0) {
            $nombre_arch              = 'sicoss';
            $periodo                  = 'Vigente_sin_retro';
            self::$archivos[$periodo] = $path . $nombre_arch;

            $legajos            = self::obtener_legajos(self::$codc_reparto, $where_periodo, $where, $datos['check_lic'], $datos['check_sin_activo']);
            $periodo            = $per_mesct . '/' . $per_anoct . ' (Vigente)';
            if ($retornar_datos === TRUE)
                return self::procesa_sicoss(
                    $datos,
                    $per_anoct,
                    $per_mesct,
                    $legajos,
                    $nombre_arch,
                    $licencias_agentes,
                    $datos['check_retro'],
                    $datos['check_sin_activo'],
                    $retornar_datos
                );
            $totales[$periodo] = self::procesa_sicoss(
                $datos,
                $per_anoct,
                $per_mesct,
                $legajos,
                $nombre_arch,
                $licencias_agentes,
                $datos['check_retro'],
                $datos['check_sin_activo'],
                $retornar_datos
            );
            $sql     =  "DROP TABLE IF EXISTS conceptos_liquidados";
            DB::connection(self::getStaticConnectionName())->statement($sql);
        } else {
            // Si tengo tildada la opcion lo que se genera es un archivo por cada periodo retro y uno para los que tiene a�o y mes retro en cero,
            // o sea, se particiona la tabla temporal que se obtiene en obtener_conceptos_liquidados

            // Periodos retro y el periodo 0-0 que va ser el periodo actual
            $periodos_retro = self::obtener_periodos_retro($datos['check_lic'], $datos['check_retro']);
            $total = [];

            for ($i = 0; $i < count($periodos_retro); $i++) {
                $p             = $periodos_retro[$i];
                $mes           = str_pad($p['mes_retro'], 2, "0", STR_PAD_LEFT);
                //agrego cero adelante a meses
                $where_periodo = "t.ano_retro=" . $p['ano_retro'] . " AND t.mes_retro=" . $mes;
                $legajos = self::obtener_legajos(self::$codc_reparto, $where_periodo, $where, $datos['check_lic'], $datos['check_sin_activo']);

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
                DB::connection(self::getStaticConnectionName())->statement($sql);

                $totales[$item] = $subtotal;
            }
        }

        // Elimino tabla temporal
        $sql = "DROP TABLE IF EXISTS pre_conceptos_liquidados";
        DB::connection(self::getStaticConnectionName())->statement($sql);

        if ($testeo_directorio_salida != '' && $testeo_prefijo_archivos != '') {
            copy(storage_path('app/comunicacion/sicoss/' . $nombre_arch . '.txt'), $testeo_directorio_salida . '/' . $testeo_prefijo_archivos);
        } else {
            // self::armar_zip();
            return self::transformar_a_recordset($totales);
        }
    }


    public static function get_licencias_protecintegral_vacaciones($where_legajos)
    {
        $fecha_inicio = self::quote(MapucheConfig::getFechaInicioPeriodoCorriente());
        $fecha_fin = self::quote(MapucheConfig::getFechaFinPeriodoCorriente());

        $variantes_vacaciones = MapucheConfig::getVarLicenciaVacaciones();
        $variantes_protecintegral = MapucheConfig::getVarLicenciaProtecintegral();

        if ($variantes_vacaciones == '' && $variantes_protecintegral == '') {
            return [];
        } else if ($variantes_vacaciones != '' && $variantes_protecintegral != '') {
            $where_vacaciones = " WHEN dh05.nrovarlicencia IN ($variantes_vacaciones) THEN '12'::integer ";
            $where_protecintegral = " WHEN dh05.nrovarlicencia IN ($variantes_protecintegral) THEN '51'::integer ";
            $variantes = $variantes_vacaciones . ',' . $variantes_protecintegral;
            $where_legajos .= "AND dh05.nrovarlicencia IN ($variantes)";
        } else {
            if ($variantes_vacaciones != '') {
                $where_vacaciones = " WHEN dh05.nrovarlicencia IN ($variantes_vacaciones) THEN '12'::integer ";
                $where_legajos .= "AND dh05.nrovarlicencia IN ($variantes_vacaciones)";
            } else if ($variantes_protecintegral != '') {
                $where_protecintegral = " WHEN dh05.nrovarlicencia IN ($variantes_protecintegral) THEN '51'::integer ";
                $where_legajos .= "AND dh05.nrovarlicencia IN ($variantes_protecintegral)";
            }
        }

        $sql = "SELECT
					dh01.nro_legaj,
					CASE
	  					WHEN dh05.fec_desde <= $fecha_inicio::date THEN date_part('day', timestamp $fecha_inicio)::integer
	  					ELSE date_part('day', dh05.fec_desde::timestamp)
					END AS inicio,
					CASE
						WHEN dh05.fec_hasta > $fecha_fin::date OR dh05.fec_hasta IS NULL THEN date_part('day', timestamp $fecha_fin)::integer
					  	ELSE date_part('day', dh05.fec_hasta::timestamp)
					END AS final,
					TRUE AS es_legajo,
					CASE
					  WHEN dl02.es_maternidad THEN '5'::integer
					  $where_vacaciones
					  $where_protecintegral
					  ELSE '13'::integer
					END AS condicion
				FROM
					mapuche.dh05
					LEFT OUTER JOIN mapuche.dl02 ON (dh05.nrovarlicencia = dl02.nrovarlicencia)
					LEFT OUTER JOIN mapuche.dh01 ON (dh05.nro_legaj = dh01.nro_legaj)
				WHERE
					dh05.nro_legaj IS NOT NULL
					AND (fec_desde <= $fecha_fin::date AND (fec_hasta is null OR fec_hasta >= $fecha_inicio::date))
					AND mapuche.map_es_licencia_vigente(dh05.nro_licencia)
					AND dl02.es_remunerada = TRUE
					AND $where_legajos

				UNION

				SELECT
					dh01.nro_legaj,
					CASE
	  					WHEN dh05.fec_desde <= $fecha_inicio::date THEN date_part('day', timestamp $fecha_inicio)::integer
	  					ELSE date_part('day', dh05.fec_desde::timestamp)
					END AS inicio,
					CASE
						WHEN dh05.fec_hasta > $fecha_fin::date OR dh05.fec_hasta IS NULL THEN date_part('day', timestamp $fecha_fin)::integer
					  	ELSE date_part('day', dh05.fec_hasta::timestamp)
					END AS final,
					FALSE AS es_legajo,
					CASE
					  WHEN dl02.es_maternidad THEN '5'::integer
					  $where_vacaciones
					  $where_protecintegral
					  ELSE '13'::integer
					END AS condicion
				FROM
					mapuche.dh05
					LEFT OUTER JOIN mapuche.dh03 ON (dh03.nro_cargo = dh05.nro_cargo)
					LEFT OUTER JOIN mapuche.dh01 ON (dh03.nro_legaj = dh01.nro_legaj)
					LEFT OUTER JOIN mapuche.dl02 ON (dh05.nrovarlicencia = dl02.nrovarlicencia)
				WHERE
					dh05.nro_cargo IS NOT NULL
					AND (fec_desde <= $fecha_fin::date AND (fec_hasta is null OR fec_hasta >= $fecha_inicio::date))
					AND mapuche.map_es_cargo_activo(dh05.nro_cargo)
					AND mapuche.map_es_licencia_vigente(dh05.nro_licencia)
					AND dl02.es_remunerada = TRUE
    				AND $where_legajos
    			;";

        $resultado = DB::connection(self::getStaticConnectionName())->select($sql);
        return array_map(function ($item) {
            return (array) $item;
        }, $resultado);
    }

    public static function get_licencias_vigentes($where_legajos): array
    {
        $fecha_inicio = self::quote(MapucheConfig::getFechaInicioPeriodoCorriente());
        $fecha_fin = self::quote(MapucheConfig::getFechaFinPeriodoCorriente());

        $variantes_ilt_primer_tramo = MapucheConfig::getVarLicencias10Dias();
        $variantes_ilt_segundo_tramo = MapucheConfig::getVarLicencias11diassiguientes();

        $variantes_down = MapucheConfig::getVarLicenciasMaternidadDown();

        $variantes_excedencia = MapucheConfig::getVarLicenciaExcedencia();

        $variantes_vacaciones = MapucheConfig::getVarLicenciaVacaciones();

        $variantes_protecintegral = MapucheConfig::getVarLicenciaProtecIntegral();

        if (isset($variantes_ilt_primer_tramo) && !empty($variantes_ilt_primer_tramo)) {
            $sql_ilt = " OR ( dh05.nrovarlicencia IN ($variantes_ilt_primer_tramo)) ";
            $where_ilt = " WHEN dh05.nrovarlicencia IN ($variantes_ilt_primer_tramo) THEN '18'::integer ";
        } else {
            $sql_ilt = '';
            $where_ilt = '';
        }

        $where_down = '';
        $where_excedencia = '';
        $where_vacaciones = '';
        $where_protecintegral = '';

        if (isset($variantes_ilt_segundo_tramo) && !empty($variantes_ilt_segundo_tramo)) {
            $sql_ilt .= " OR ( dh05.nrovarlicencia IN ($variantes_ilt_segundo_tramo)) ";
            $where_ilt .= " WHEN dh05.nrovarlicencia IN ($variantes_ilt_segundo_tramo) THEN '19'::integer ";
        }

        if (isset($variantes_down) && !empty($variantes_down)) {
            $where_down = " WHEN dh05.nrovarlicencia IN ($variantes_down) THEN '11'::integer ";
        }

        if (isset($variantes_excedencia) && !empty($variantes_excedencia)) {
            $where_excedencia = " WHEN dh05.nrovarlicencia IN ($variantes_excedencia) THEN '10'::integer ";
        }

        if (isset($variantes_vacaciones) && !empty($variantes_vacaciones)) {
            $where_vacaciones = " WHEN dh05.nrovarlicencia IN ($variantes_vacaciones) THEN '12'::integer ";
        }

        if (isset($variantes_protecintegral) && !empty($variantes_protecintegral)) {
            $where_protecintegral = " WHEN dh05.nrovarlicencia IN ($variantes_protecintegral) THEN '51'::integer ";
        }

        $where_norem = "(dl02.es_maternidad IS TRUE OR
   							(NOT dl02.es_remunerada OR
   								(dl02.es_remunerada AND dl02.porcremuneracion = '0')
   							)
   						 	$sql_ilt
   						)";

        $sql = "SELECT
					dh01.nro_legaj,
					CASE
	  					WHEN dh05.fec_desde <= $fecha_inicio::date THEN date_part('day', timestamp $fecha_inicio)::integer
	  					ELSE date_part('day', dh05.fec_desde::timestamp)
					END AS inicio,
					CASE
						WHEN dh05.fec_hasta > $fecha_fin::date OR dh05.fec_hasta IS NULL THEN date_part('day', timestamp $fecha_fin)::integer
					  	ELSE date_part('day', dh05.fec_hasta::timestamp)
					END AS final,
					TRUE AS es_legajo,
					CASE
					  $where_down
					  WHEN dl02.es_maternidad THEN '5'::integer
					  $where_excedencia
					  $where_ilt
					  $where_vacaciones
					  $where_protecintegral
					  ELSE '13'::integer
					END AS condicion
				FROM
					mapuche.dh05
					LEFT OUTER JOIN mapuche.dl02 ON (dh05.nrovarlicencia = dl02.nrovarlicencia)
					LEFT OUTER JOIN mapuche.dh01 ON (dh05.nro_legaj = dh01.nro_legaj)
				WHERE
					dh05.nro_legaj IS NOT NULL
					AND (fec_desde <= $fecha_fin::date AND (fec_hasta is null OR fec_hasta >= $fecha_inicio::date))
					AND mapuche.map_es_licencia_vigente(dh05.nro_licencia)
					AND $where_norem
					AND $where_legajos

				UNION

				SELECT
					dh01.nro_legaj,
					CASE
	  					WHEN dh05.fec_desde <= $fecha_inicio::date THEN date_part('day', timestamp $fecha_inicio)::integer
	  					ELSE date_part('day', dh05.fec_desde::timestamp)
					END AS inicio,
					CASE
						WHEN dh05.fec_hasta > $fecha_fin::date OR dh05.fec_hasta IS NULL THEN date_part('day', timestamp $fecha_fin)::integer
					  	ELSE date_part('day', dh05.fec_hasta::timestamp)
					END AS final,
					FALSE AS es_legajo,
					CASE
					  $where_down
					  WHEN dl02.es_maternidad THEN '5'::integer
					  $where_excedencia
					  $where_ilt
					  $where_vacaciones
					  $where_protecintegral
					  ELSE '13'::integer
					END AS condicion
				FROM
					mapuche.dh05
					LEFT OUTER JOIN mapuche.dh03 ON (dh03.nro_cargo = dh05.nro_cargo)
					LEFT OUTER JOIN mapuche.dh01 ON (dh03.nro_legaj = dh01.nro_legaj)
					LEFT OUTER JOIN mapuche.dl02 ON (dh05.nrovarlicencia = dl02.nrovarlicencia)
				WHERE
					dh05.nro_cargo IS NOT NULL
					AND (fec_desde <= $fecha_fin::date AND (fec_hasta is null OR fec_hasta >= $fecha_inicio::date))
					AND mapuche.map_es_cargo_activo(dh05.nro_cargo)
					AND mapuche.map_es_licencia_vigente(dh05.nro_licencia)
					AND $where_norem
    				AND $where_legajos
    			;";

        $licencias = DB::connection(self::getStaticConnectionName())->select($sql);
        return array_map(function ($item) {
            return (array) $item;
        }, $licencias);
    }

    static function get_cargos_activos_sin_licencia($legajo): array
    {
        $fecha_inicio = self::quote(MapucheConfig::getFechaInicioPeriodoCorriente());
        $fecha_fin = self::quote(MapucheConfig::getFechaFinPeriodoCorriente());

        $legajo = self::quote($legajo);

        $sql = "SELECT
			    	dh03.nro_cargo,
					CASE
	  					WHEN fec_alta <= $fecha_inicio::date THEN date_part('day', timestamp $fecha_inicio)::integer
	  					ELSE date_part('day', fec_alta::timestamp)
					END AS inicio,
					CASE
						WHEN fec_baja > $fecha_fin::date OR fec_baja IS NULL THEN date_part('day', timestamp $fecha_fin)::integer
					  	ELSE date_part('day', fec_baja::timestamp)
					END AS final
		    	FROM
		    		mapuche.dh03 dh03
		    	WHERE
			    	(fec_baja IS NULL OR fec_baja >= $fecha_inicio::date) and
			    	dh03.nro_legaj = $legajo and
			    	dh03.nro_cargo NOT IN ( SELECT
			    								dh05.nro_cargo
			    							FROM
			    								mapuche.dh05
			    								JOIN mapuche.dl02 ON (dh05.nrovarlicencia = dl02.nrovarlicencia)
			    							WHERE
			    								dh05.nro_cargo = dh03.nro_cargo AND
			    								mapuche.map_es_licencia_vigente(dh05.nro_licencia) AND
			    								(dl02.es_maternidad IS TRUE OR (NOT dl02.es_remunerada OR (dl02.es_remunerada AND dl02.porcremuneracion = '0')))
			    								)
    			;";
        $resultado = DB::connection(self::getStaticConnectionName())->select($sql);
        return array_map(function ($item) {
            return (array) $item;
        }, $resultado);
    }

    static function get_cargos_activos_con_licencia_vigente($legajo): array
    {
        $fecha_inicio = self::quote(MapucheConfig::getFechaInicioPeriodoCorriente());
        $fecha_fin = self::quote(MapucheConfig::getFechaFinPeriodoCorriente());

        $legajo = self::quote($legajo);

        $sql = "SELECT
    				dh03.nro_cargo,
			    	CASE
			    		WHEN fec_alta <= $fecha_inicio::date THEN date_part('day', timestamp $fecha_inicio)::integer
			    		ELSE date_part('day', fec_alta::timestamp)
			    	END AS inicio,
			    	CASE
			 		   	WHEN fec_baja > $fecha_fin::date OR fec_baja IS NULL THEN date_part('day', timestamp $fecha_fin)::integer
			  	  		ELSE date_part('day', fec_baja::timestamp)
			    	END AS final,

			    	CASE
			    		WHEN fec_desde <= $fecha_inicio::date THEN date_part('day', timestamp $fecha_inicio)::integer
			    		ELSE date_part('day', fec_desde::timestamp)
			    	END AS inicio_lic,
			    	CASE
			 		   	WHEN fec_hasta > $fecha_fin::date OR fec_hasta IS NULL THEN date_part('day', timestamp $fecha_fin)::integer
			  	  		ELSE date_part('day', fec_hasta::timestamp)
			    	END AS final_lic,
			    	CASE
					  WHEN dl02.es_maternidad THEN '5'::integer
					  ELSE
						CASE
							WHEN dl02.es_remunerada THEN '1'::integer
							ELSE '13'::integer
							END
					END AS condicion

    			FROM
    				mapuche.dh03 dh03,
    				mapuche.dh05 dh05
    			LEFT OUTER JOIN mapuche.dl02 ON (dh05.nrovarlicencia = dl02.nrovarlicencia)
    			WHERE
    				dh05.nro_cargo = dh03.nro_cargo AND
			    	(fec_baja IS NULL OR fec_baja >= $fecha_inicio::date) AND
			    	(fec_desde <= $fecha_fin::date AND fec_hasta >= $fecha_inicio::date) AND
			    	dh03.nro_legaj = $legajo AND
			    	dh03.nro_cargo NOT IN ( SELECT	dh05.nro_cargo
			    							FROM
			    								mapuche.dh05
			    							JOIN mapuche.dl02 ON (dh05.nrovarlicencia = dl02.nrovarlicencia)
			    							WHERE
						    					dh05.nro_cargo = dh03.nro_cargo AND
						    					mapuche.map_es_licencia_vigente(dh05.nro_licencia)
						    					AND (dh05.fec_desde < mapuche.map_get_fecha_inicio_periodo() - 1)  AND
						    					(dl02.es_maternidad IS TRUE OR (NOT dl02.es_remunerada OR (dl02.es_remunerada AND dl02.porcremuneracion = '0')))
						    				)
    			;";
        $resultado = DB::connection(self::getStaticConnectionName())->select($sql);
        return array_map(function ($item) {
            return (array) $item;
        }, $resultado);
    }

    static function get_limites_cargos($legajo): array
    {
        $fecha_inicio = self::quote(MapucheConfig::getFechaInicioPeriodoCorriente());
        $fecha_fin = self::quote(MapucheConfig::getFechaFinPeriodoCorriente());

        $legajo = self::quote($legajo);

        $sql = "SELECT
    				CASE
    					WHEN MIN(fec_alta) > $fecha_inicio::date THEN date_part('day', MIN(fec_alta)::timestamp)
						ELSE date_part('day', timestamp $fecha_inicio)::integer
					END AS minimo,
					MAX(CASE
						WHEN fec_baja > $fecha_fin::date OR
							 fec_baja IS NULL THEN date_part('day', timestamp $fecha_fin)::integer
						ELSE date_part('day', fec_baja::timestamp)
					END) AS maximo
				FROM
					mapuche.dh03
				WHERE
					(fec_baja IS NULL OR fec_baja >= $fecha_inicio::date) AND
					nro_legaj = $legajo
    			;";

        $resultado = DB::connection(self::getStaticConnectionName())->select($sql);
        return array_map(function ($item) {
            return (array) $item;
        }, $resultado);
    }

    static function obtener_conceptos_liquidados($per_anoct, $per_mesct, $where)
    {
        // Se hace una consulta de todos los conceptos liquidados del periodo vigente, que generen impuestos cuyos conceptos sean mayores a cero
        // Se arma una columna tipos_grupos que va tener todos los numeros tipo de grupo a los cuales pertenece el concepto
        // Se guardan los datos en una tabla temporal: pre_conceptos_liquidados

        $sql_conceptos_liq = "CREATE TEMP TABLE pre_conceptos_liquidados AS
        WITH tipos_grupos_conceptos AS (
            SELECT
                dh16.codn_conce,
                array_agg(DISTINCT dh15.codn_tipogrupo) AS tipos_grupos
            FROM mapuche.dh16
            INNER JOIN mapuche.dh15 ON dh15.codn_grupo = dh16.codn_grupo
            GROUP BY dh16.codn_conce
        )
        SELECT DISTINCT
            dh21.id_liquidacion,
            dh21.impp_conce,
            dh21.ano_retro,
            dh21.mes_retro,
            dh21.nro_legaj,
            dh21.codn_conce,
            dh21.tipo_conce,
            dh21.nro_cargo,
            dh21.nov1_conce,
            dh12.nro_orimp,
            COALESCE(tgc.tipos_grupos, ARRAY[]::integer[]) AS tipos_grupos,
            dh21.codigoescalafon
        FROM mapuche.dh21
        INNER JOIN mapuche.dh22 ON dh22.nro_liqui = dh21.nro_liqui
        LEFT JOIN mapuche.dh01 ON dh01.nro_legaj = dh21.nro_legaj
        LEFT JOIN mapuche.dh12 ON dh12.codn_conce = dh21.codn_conce
        LEFT JOIN tipos_grupos_conceptos tgc ON tgc.codn_conce = dh21.codn_conce
        WHERE dh22.per_liano = " . $per_anoct . "
        AND dh22.per_limes = " . $per_mesct . "
        AND dh22.sino_genimp = true
        AND dh21.codn_conce > 0
        AND $where";
        $rs = DB::connection(self::getStaticConnectionName())->select($sql_conceptos_liq);

        $sql_ix = "CREATE INDEX ix_pre_conceptos_liquidados_1 ON pre_conceptos_liquidados(id_liquidacion);";
        DB::connection(self::getStaticConnectionName())->select($sql_ix);

        // Índices adicionales para optimizar filtros posteriores
        $sql_ix2 = "CREATE INDEX ix_pre_conceptos_liquidados_periodo ON pre_conceptos_liquidados(ano_retro, mes_retro);";
        DB::connection(self::getStaticConnectionName())->select($sql_ix2);
    }

    public static function obtener_periodos_retro($check_lic = false, $check_retr = false)
    {
        // Obtengo los a�o y mes retro disponibles del periodo de la tabla temporal que genero para hacer hacer el join con la tabla de legajos
        $rs_periodos_retro = [];
        if ($check_lic && $check_retr) {
            $sql_periodos_retro = "
                                    SELECT
                                            DISTINCT(ano_retro),mes_retro
                                    FROM
                                            pre_conceptos_liquidados
                                    ORDER BY
                                            ano_retro desc, mes_retro desc
                                    ";
            $rs_periodos_retro = DB::connection(self::getStaticConnectionName())->select($sql_periodos_retro);
            $temp['ano_retro'] = '0';
            $temp['mes_retro'] = '0';
            array_push($rs_periodos_retro, $temp);
        } elseif (!$check_lic) {
            $sql_periodos_retro = "
                                    SELECT
                                            DISTINCT(ano_retro),mes_retro
                                    FROM
                                            pre_conceptos_liquidados
                                    ORDER BY
                                            ano_retro desc, mes_retro desc
                                    ";
            $rs_periodos_retro = DB::connection(self::getStaticConnectionName())->select($sql_periodos_retro);
        }
        // si tiene check de licencias solo debo tener en cuenta el periodo retro 0-0
        else {
            $temp['ano_retro'] = '0';
            $temp['mes_retro'] = '0';
            array_push($rs_periodos_retro, $temp);
        }
        return $rs_periodos_retro;
    }

    public static function obtener_legajos($codc_reparto, $where_periodo_retro, $where_legajo = ' true ', $check_lic = false, $check_sin_activo = false)
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

        $rs_filtrado = DB::connection(self::getStaticConnectionName())->select($sql_conceptos_liq_filtrados);


        $sql_ix = "CREATE INDEX ix_conceptos_liquidados_1 ON conceptos_liquidados(nro_legaj,tipos_grupos);";
        $rs_filtrado = DB::connection(self::getStaticConnectionName())->select($sql_ix);
        $sql_ix = "CREATE INDEX ix_conceptos_liquidados_2 ON conceptos_liquidados(nro_legaj,tipo_conce);";
        $rs_filtrado = DB::connection(self::getStaticConnectionName())->select($sql_ix);

        // Se obtienen datos por legajo, de los numeros de legajos liquidados en la tabla anterior conceptos_liquidados
        // si en los datos del legajo licencia es igual a cero es que el legajo no tenia licencias o no algun concepto liquidado
        $sql_datos_legajo = self::get_sql_legajos('conceptos_liquidados', 0);

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
                $sql_datos_lic = ' UNION (' . self::get_sql_legajos("mapuche..dh01", 1, $where) . ' ORDER BY apyno';

                $legajos = DB::connection(self::getStaticConnectionName())->select($sql_datos_legajo . $sql_datos_lic);
            } else {
                $sql_datos_legajo .= ' ORDER BY apyno';
                // Si no hay licencias sin goce que cumpaln con las restricciones hago el proceso comun
                $legajos = DB::connection(self::getStaticConnectionName())->select($sql_datos_legajo);
            }
        } else {
            $sql_datos_legajo .= ' ORDER BY apyno';
            // Si no tengo el check licencias se consulta solo contra conceptos liquidados
            $legajos = DB::connection(self::getStaticConnectionName())->select($sql_datos_legajo);
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

            $sql_legajos_no_liquidados = self::get_sql_legajos("mapuche..dh01", 0, $where_no_liquidado);
            $legajos_t = DB::connection(self::getStaticConnectionName())->select($sql_legajos_no_liquidados);
            $legajos = array_merge($legajos, $legajos_t);
        }

        // Elimino legajos repetidos
        $legajos_sin_repetidos = [];
        foreach ($legajos as $legajo) {
            if (isset($legajos_sin_repetidos[$legajo['nro_legaj']])) {
                if ($legajos_sin_repetidos[$legajo['nro_legaj']]['licencia'] == 1)
                    $legajos_sin_repetidos[$legajo['nro_legaj']] = $legajo;
            } else
                $legajos_sin_repetidos[$legajo['nro_legaj']] = $legajo;
        }
        $legajos = [];
        foreach ($legajos_sin_repetidos as $legajo)
            $legajos[] = $legajo;

        return $legajos;
    }

    /*
	* Tanto para los legajos con licencia y para conceptos liquidados se necesitan los mismos campos; uno usa dh01
	* y otro conceptos liquidados, por eso se parametriza el string-sql
	*/
    public static function get_sql_legajos($tabla, $valor, $where = ' true ')
    {
        $vacio = self::quote("");
        $sql = "
				SELECT
						DISTINCT(dh01.nro_legaj),
						(dh01.nro_cuil1::char(2)||LPAD(dh01.nro_cuil::char(8),8,'0')||dh01.nro_cuil2::char(1))::float8 AS cuit,
						dh01.desc_appat||' '||dh01.desc_nombr AS apyno,
						dh01.tipo_estad AS estado,
						(SELECT
								COUNT(*)
							FROM
								mapuche.dh02
							WHERE
							dh02.nro_legaj = dh01.nro_legaj AND dh02.sino_cargo!='N' AND dh02.codc_paren ='CONY'
						) AS conyugue,
						(SELECT
								COUNT(*)
						FROM
								mapuche.dh02
						WHERE
								dh02.nro_legaj=dh01.nro_legaj AND dh02.sino_cargo!='N' AND dh02.codc_paren IN ('HIJO', 'HIJN', 'HINC' ,'HINN' )
						) AS hijos,
						dha8.ProvinciaLocalidad,
						dha8.codigosituacion,
						dha8.CodigoCondicion,
						dha8.codigozona,
						dha8.CodigoActividad,
						dha8.porcaporteadicss AS aporteAdicional,
						dha8.trabajador_convencionado AS trabajadorconvencionado,
						dha8.codigomodalcontrat AS codigocontratacion,
						CASE WHEN ((dh09.codc_bprev = " . self::$codc_reparto . " ) OR (dh09.fuerza_reparto) OR ((" . self::$codc_reparto . " = $vacio) AND (dh09.codc_bprev IS NULL)))THEN '1'
						ELSE '0'
						END AS regimen,
						dh09.cant_cargo AS adherentes,
						$valor AS licencia,
						0 AS importeimponible_9
					FROM
						$tabla
						LEFT OUTER JOIN mapuche.dh02 ON dh02.nro_legaj = $tabla.nro_legaj
						LEFT OUTER JOIN mapuche.dha8 ON dha8.nro_legajo = $tabla.nro_legaj
						LEFT OUTER JOIN mapuche.dh09 ON dh09.nro_legaj = $tabla.nro_legaj
						LEFT OUTER JOIN mapuche.dhe9 ON dhe9.nro_legaj = $tabla.nro_legaj ";

        // Si la tabla es dh01 no necesito el join con la misma tabla
        if ($tabla != "mapuche..dh01")
            $sql .= " LEFT OUTER JOIN mapuche.dh01 ON $tabla.nro_legaj = dh01.nro_legaj ";

        $sql .= " WHERE  $where";
        return $sql;
    }



    static function inicializar_estado_situacion($codigo, $min, $max)
    {
        $periodo = MapucheConfig::getPeriodoCorriente();
        $estado_situacion = [];
        for ($i = $min; $i <= $max; $i++) {
            $estado_situacion[$i] = $codigo;
        }
        return $estado_situacion;
    }

    /**
     * Se le pasa la condici�n actual y se compara con la condici�n
     * obtenida a partir del tipo de licencia (5 => maternidad o 13 => no remunerada o 18/19 => ILT)
     *
     * @param integer $c1 : condici�n actual
     * @param integer $c2 : condici�n tipo de licencia
     *
     * @return integer $c1
     */
    static function evaluar_condicion_licencia($c1, $c2)
    {
        // Maternidad primero
        if ($c1 == 5 || $c2 == 5) {
            return 5;
        } else if ($c1 == 11 || $c2 == 11) {
            return 11;
        } else if ($c1 == 10 || $c2 == 10) {
            return 10;
        } elseif ($c1 == 18 || $c2 == 18) {
            return 18;
        } else if ($c1 == 19 || $c2 == 19) {
            return 19;
        } else if ($c1 == 13 || $c2 == 13) {
            return 13;
        } else if ($c1 == 12 || $c2 == 12) {
            return 12;
        } else if ($c1 == 51 || $c2 == 51) {
            return 51;
        }

        return $c1; // Por defecto se retorna la condici�n actual
    }

    static function calcular_cambios_estado($estado_situacion)
    {
        $cambios = [];

        foreach ($estado_situacion as $dia => $codigo) {
            if (!isset($anterior) || $anterior != $codigo) {
                $cambios[$dia] = $codigo;
            }
            $anterior = $codigo;
        }

        return $cambios;
    }

    static function calcular_dias_trabajados($estado_situacion)
    {
        $dias_trabajados = 0;
        foreach ($estado_situacion as $codigo) {
            // Se suman solo los dias trabajados, codigo 1
            // Los dias de Licencia por Maternidad tmb cuentan como trabajados
            if ($codigo === 1 || $codigo === 5 || $codigo === 12 || $codigo === 51) {
                $dias_trabajados += 1;
            }
        }

        return $dias_trabajados;
    }

    static function calcular_revista_legajo($cambios_estado)
    {
        $controlar_maternidad = false;
        $revista_legajo = [];
        $cantidad_cambios = count($cambios_estado);
        $dias = array_keys($cambios_estado);

        $revista_legajo[1] = array('codigo' => 0, 'dia' => 0);
        $revista_legajo[2] = array('codigo' => 0, 'dia' => 0);
        $revista_legajo[3] = array('codigo' => 0, 'dia' => 0);

        $primer_dia = 0;

        if ($cantidad_cambios > 3) {
            $primer_dia = $cantidad_cambios - 3;
            $controlar_maternidad = true;
        }

        $revista = 1;
        for ($i = $primer_dia; $i < $cantidad_cambios; $i++) {
            $dia = $dias[$i];
            $revista_legajo[$revista] = array('codigo' => $cambios_estado[$dia], 'dia' => $dia);
            $revista++;
        }

        if ($controlar_maternidad) {
            $dia_revista = $revista_legajo[1]['dia'];
            foreach ($cambios_estado as $dia => $situacion) {
                if (($situacion == 5) && ($dia < $dia_revista)) {
                    $revista_legajo[1]['dia']        = $dia;
                    $revista_legajo[1]['codigo']     = $situacion;
                }
            }
        }

        return $revista_legajo;
    }

    public static function procesa_sicoss($datos, $per_anoct, $per_mesct, $legajos, $nombre_arch, $licencias = NULL, $retro = FALSE, $check_sin_activo = FALSE, $retornar_datos = FALSE)
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
        $total_legajos = count($legajos);
        $j = 0;

        // En este for se completan los campos necesarios para cada uno de los legajos liquidados
        for ($i = 0; $i < $total_legajos; $i++) {
            $legajo = $legajos[$i]['nro_legaj'];
            $legajoActual = &$legajos[$i];

            $legajoActual['ImporteSACOtroAporte'] = 0;
            $legajoActual['TipoDeOperacion']      = 0;
            $legajoActual['ImporteImponible_4']   = 0;
            $legajoActual['ImporteSACNoDocente']  = 0;

            $legajoActual['ImporteSACDoce']  = 0;
            $legajoActual['ImporteSACAuto']  = 0;

            $legajoActual['codigo_os'] = self::codigo_os($legajo);

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
                        $legajoActual['codigosituacion'] = 14;
                }
            }

            if (!$retro) {
                $limites = self::get_limites_cargos($legajo);
                //En caso de que el agente no tenga cargos activos, pero aparezca liquidado.
                if (!isset($limites['maximo'])) {
                    $cargos_activos_agente = Dh03::getCargosActivos($legajo);
                    if (empty($cargos_activos_agente)) {
                        $fecha_fin = self::quote(MapucheConfig::getFechaFinPeriodoCorriente());
                        $limites['maximo'] = substr($fecha_fin, 9, 2);
                    }
                }
                $estado_situacion = self::inicializar_estado_situacion($legajoActual['codigosituacion'], $limites['minimo'], $limites['maximo']);

                $cargos_legajo = self::get_cargos_activos_sin_licencia($legajo);
                $cargos_legajo2 = self::get_cargos_activos_con_licencia_vigente($legajo);
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
                                    if ($limites['maximo'] >= $dia)
                                        $estado_situacion[$dia] = self::evaluar_condicion_licencia($estado_situacion[$dia], $licencia['condicion']);
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

                $cambios_estado = self::calcular_cambios_estado($estado_situacion);
                $dias_trabajados = self::calcular_dias_trabajados($estado_situacion);
                $revista_legajo = self::calcular_revista_legajo($cambios_estado);


                // Como c�digo de situaci�n general se toma el �ltimo (?)
                $legajoActual['codigosituacion'] = $estado_situacion[$limites['maximo']];
                // Revista 1
                $legajoActual['codigorevista1'] = $revista_legajo[1]['codigo'];
                $legajoActual['fecharevista1'] = $revista_legajo[1]['dia'];
                // Revista 2
                if ($revista_legajo[2]['codigo'] == 0) {
                    $legajoActual['codigorevista2'] = $revista_legajo[1]['codigo'];
                } else {
                    $legajoActual['codigorevista2'] = $revista_legajo[2]['codigo'];
                }
                $legajoActual['fecharevista2'] = $revista_legajo[2]['dia'];

                // Revista 3
                if ($revista_legajo[3]['codigo'] == 0) {
                    $legajoActual['codigorevista3'] = $legajoActual['codigorevista2'];
                } else {
                    $legajoActual['codigorevista3'] = $revista_legajo[3]['codigo'];
                }
                $legajoActual['fecharevista3'] = $revista_legajo[3]['dia'];

                // Como d�as trabajados se toman aquellos d�as de cargo menos los d�as de licencia sin goce (?)
                $legajoActual['dias_trabajados'] = $dias_trabajados;
            } else {
                // Se evaluan

                // Si tiene una licencia por maternidad activa el codigo de situacion es 5
                if (LicenciaService::tieneLicenciaMaternidadActiva($legajo)) {
                    $legajoActual['codigosituacion'] = 5;
                }

                // Si tengo chequeado el tilde de licencias cambio el codigo de situacion y la cantidad de dias trabajados se vuelve 0
                if ($datos['check_lic'] && ($legajoActual['licencia'] == 1)) {
                    $legajoActual['codigosituacion'] = 13;
                    $legajoActual['dias_trabajados'] = '00';
                } else {
                    $legajoActual['dias_trabajados'] = '30';
                }

                $legajoActual['codigorevista1'] = $legajoActual['codigosituacion'];
                $legajoActual['fecharevista1'] = '01';
                $legajoActual['codigorevista2'] = '00';
                $legajoActual['fecharevista2'] = '00';
                $legajoActual['codigorevista3'] = '00';
                $legajoActual['fecharevista3'] = '00';
            }

            // Se informa solo si tiene conyugue o no; no la cantidad
            if ($legajoActual['conyugue'] > 0)
                $legajoActual['conyugue'] = 1;

            // --- Obtengo la sumarizaci�n seg�n concepto � tipo de grupo de un concepto ---
            self::sumarizar_conceptos_por_tipos_grupos($legajo, $legajoActual);

            // --- Otros datos remunerativos ---

            // Sumarizar conceptos segun tipo de concepto
            $suma_conceptos_tipoC = self::calcular_remuner_grupo($legajo, 'C', 'nro_orimp >0 AND codn_conce > 0');
            $suma_conceptos_tipoF = self::calcular_remuner_grupo($legajo, 'F', 'true');

            $legajoActual['Remuner78805']               = $suma_conceptos_tipoC;
            $legajoActual['AsignacionesFliaresPagadas'] = $suma_conceptos_tipoF;
            $legajoActual['ImporteImponiblePatronal']   = $suma_conceptos_tipoC;

            // Para calcular Remuneracion total= IMPORTE_BRUTO
            $legajoActual['DiferenciaSACImponibleConTope'] = 0;
            $legajoActual['DiferenciaImponibleConTope']    = 0;
            $legajoActual['ImporteSACPatronal']            = $legajoActual['ImporteSAC'];
            $legajoActual['ImporteImponibleSinSAC']        = $legajoActual['ImporteImponiblePatronal'] - $legajoActual['ImporteSACPatronal'];
            if ($legajoActual['ImporteSAC'] > $TopeSACJubilatorioPatr  && $trunca_tope == 1) {
                $legajoActual['DiferenciaSACImponibleConTope'] = $legajoActual['ImporteSAC'] - $TopeSACJubilatorioPatr;
                $legajoActual['ImporteImponiblePatronal']  -= $legajoActual['DiferenciaSACImponibleConTope'];
                $legajoActual['ImporteSACPatronal']         = $TopeSACJubilatorioPatr;
            }

            if ($legajoActual['ImporteImponibleSinSAC'] > $TopeJubilatorioPatronal && $trunca_tope == 1) {
                $legajoActual['DiferenciaImponibleConTope'] = $legajoActual['ImporteImponibleSinSAC'] - $TopeJubilatorioPatronal;
                $legajoActual['ImporteImponiblePatronal']  -= $legajoActual['DiferenciaImponibleConTope'];
            }

            $legajoActual['IMPORTE_BRUTO'] = $legajoActual['ImporteImponiblePatronal'] + $legajoActual['ImporteNoRemun'];

            // Para calcular IMPORTE_IMPON que es lo mismo que importe imponible 1
            $legajoActual['IMPORTE_IMPON'] = 0;
            $legajoActual['IMPORTE_IMPON'] = $suma_conceptos_tipoC;

            $VerificarAgenteImportesCERO  = 1;

            // Si es el check de informar becarios en configuracion esta chequeado entonces sumo al importe imponible la suma de conceptos de ese tipo de grupo (Becarios ART)
            if ($legajoActual['ImporteImponibleBecario'] != 0) {
                $legajoActual['IMPORTE_IMPON']            += $legajoActual['ImporteImponibleBecario'];
                $legajoActual['IMPORTE_BRUTO']            += $legajoActual['ImporteImponibleBecario'];
                $legajoActual['ImporteImponiblePatronal'] += $legajoActual['ImporteImponibleBecario'];
                $legajoActual['Remuner78805']             += $legajoActual['ImporteImponibleBecario'];
            }

            if (self::VerificarAgenteImportesCERO($legajoActual) == 1 || $legajoActual['codigosituacion'] == 5 || $legajoActual['codigosituacion'] == 11) // codigosituacion=5 y codigosituacion=11 quiere decir maternidad y debe infrormarse
            {
                $legajoActual['PorcAporteDiferencialJubilacion'] = self::$porc_aporte_adicional_jubilacion;
                $legajoActual['ImporteImponible_4']              = $legajoActual['IMPORTE_IMPON'];
                $legajoActual['ImporteSACNoDocente']             = 0;
                //ImporteImponible_6 viene con valor de funcion sumarizar_conceptos_por_tipos_grupos
                $legajoActual['ImporteImponible_6']              = round((($legajoActual['ImporteImponible_6'] * 100) / $legajoActual['PorcAporteDiferencialJubilacion']), 2);
                $Imponible6_aux                                 = $legajoActual['ImporteImponible_6'];
                if ($Imponible6_aux != 0) {
                    if (
                        (int)$Imponible6_aux != (int)$legajoActual['IMPORTE_IMPON']
                        && (abs($Imponible6_aux - $legajoActual['IMPORTE_IMPON'])) > 5 //redondear hasta + � - $5
                        && $legajoActual['ImporteImponible_6'] < $legajoActual['IMPORTE_IMPON']
                    ) {
                        $legajoActual['TipoDeOperacion']     = 2;
                        $legajoActual['IMPORTE_IMPON']       = $legajoActual['IMPORTE_IMPON'] - $legajoActual['ImporteImponible_6'];
                        $legajoActual['ImporteSACNoDocente'] = $legajoActual['ImporteSAC'] - $legajoActual['SACInvestigador'];
                    } else {
                        if ((($Imponible6_aux + 5) > $legajoActual['IMPORTE_IMPON'])
                            && (($Imponible6_aux - 5) < $legajoActual['IMPORTE_IMPON'])
                        ) {
                            $legajoActual['ImporteImponible_6'] = $legajoActual['IMPORTE_IMPON'];
                        } else {
                            $legajoActual['ImporteImponible_6'] = $Imponible6_aux;
                        }
                        $legajoActual['TipoDeOperacion']     = 1;
                        $legajoActual['ImporteSACNoDocente'] = $legajoActual['ImporteSAC'];
                    }
                } else {
                    $legajoActual['TipoDeOperacion']     = 1;
                    $legajoActual['ImporteSACNoDocente'] = $legajoActual['ImporteSAC'];
                }

                $legajoActual['ImporteSACOtroAporte']          = $legajoActual['ImporteSAC'];
                $legajoActual['DiferenciaSACImponibleConTope'] = 0;
                $legajoActual['DiferenciaImponibleConTope']    = 0;

                /*****************/

                $tope_jubil_personal = $TopeJubilatorioPersonal;
                if ($legajoActual['ImporteSAC'] > 0)
                    $tope_jubil_personal = $TopeJubilatorioPersonal + $TopeSACJubilatorioPers;


                if ($legajoActual['ImporteSACNoDocente']  > $tope_jubil_personal) {
                    if ($trunca_tope == 1) {
                        $legajoActual['DiferenciaSACImponibleConTope'] = $legajoActual['ImporteSACNoDocente']  - $TopeSACJubilatorioPers;
                        $legajoActual['IMPORTE_IMPON']                -= $legajoActual['DiferenciaSACImponibleConTope'];
                        $legajoActual['ImporteSACNoDocente']           = $TopeSACJubilatorioPers;
                    }
                } else {

                    if ($trunca_tope == 1) {

                        $bruto_nodo_sin_sac = $legajoActual['IMPORTE_BRUTO'] - $legajoActual['ImporteImponible_6'] - $legajoActual['ImporteSACNoDocente'];

                        $sac = $legajoActual['ImporteSACNoDocente'];

                        $tope = min($bruto_nodo_sin_sac, $TopeJubilatorioPersonal) + min($sac, $TopeSACJubilatorioPers);
                        $imp_1 =  $legajoActual['IMPORTE_BRUTO'] -  $legajoActual['ImporteImponible_6'];

                        $tope_sueldo = min($bruto_nodo_sin_sac - $legajoActual['ImporteNoRemun'], $TopeJubilatorioPersonal);
                        $tope_sac = min($sac, $TopeSACJubilatorioPers);


                        $legajoActual['IMPORTE_IMPON'] = min($bruto_nodo_sin_sac - $legajoActual['ImporteNoRemun'], $TopeJubilatorioPersonal) + min($sac, $TopeSACJubilatorioPers);
                    }
                }

                $explode = explode(',', self::$categoria_diferencial ?? ''); //arma el array
                $implode = implode("','", $explode); //vulve a String y agrega comillas
                $instance = new Dh03Repository();
                if ($instance->existeCategoriaDiferencial($legajoActual['nro_legaj'], $implode)) {
                    $legajoActual['IMPORTE_IMPON'] = 0;
                }

                $legajoActual['ImporteImponibleSinSAC'] = $legajoActual['IMPORTE_IMPON'] - $legajoActual['ImporteSACNoDocente'];


                $tope_jubil_personal = $TopeJubilatorioPersonal;
                if ($legajoActual['ImporteSAC'] > 0)
                    $tope_jubil_personal = $TopeJubilatorioPersonal + $TopeSACJubilatorioPers;
                else
                    $tope_jubil_personal = $TopeJubilatorioPersonal;

                if ($legajoActual['ImporteImponibleSinSAC']  > $tope_jubil_personal) {
                    if ($trunca_tope == 1) {
                        $legajoActual['DiferenciaImponibleConTope'] = $legajoActual['ImporteImponibleSinSAC'] - $TopeJubilatorioPersonal;
                        $legajoActual['IMPORTE_IMPON']             -= $legajoActual['DiferenciaImponibleConTope'];
                    }
                }


                $otra_actividad = self::otra_actividad($legajo);
                $legajoActual['ImporteBrutoOtraActividad']  = $otra_actividad['importebrutootraactividad'];
                $legajoActual['ImporteSACOtraActividad']    = $otra_actividad['importesacotraactividad'];

                if (($legajoActual['ImporteBrutoOtraActividad'] != 0) || ($legajoActual['ImporteSACOtraActividad'] != 0)) {
                    if (($legajoActual['ImporteBrutoOtraActividad'] + $legajoActual['ImporteSACOtraActividad'])  >=  ($TopeSACJubilatorioPers + $TopeJubilatorioPatronal)) {
                        $legajoActual['IMPORTE_IMPON'] = 0.00;
                    } else {
                        $imp_1_tope = 0.0;
                        $imp_1_tope_sac = 0.0;

                        if ($TopeJubilatorioPersonal > $legajoActual['ImporteBrutoOtraActividad']) {
                            $imp_1_tope += $TopeJubilatorioPersonal - $legajoActual['ImporteBrutoOtraActividad'];
                        }

                        if ($TopeSACJubilatorioPers > $legajoActual['ImporteSACOtraActividad']) {
                            $imp_1_tope_sac += $TopeSACJubilatorioPers - $legajoActual['ImporteSACOtraActividad'];
                        }

                        if ($imp_1_tope > $legajoActual['ImporteImponibleSinSAC']) {
                            $imp_1_tope = $legajoActual['ImporteImponibleSinSAC'];
                        }

                        if ($imp_1_tope_sac > $legajoActual['ImporteSACPatronal']) {
                            $imp_1_tope_sac = $legajoActual['ImporteSACPatronal'];
                        }

                        $legajoActual['IMPORTE_IMPON'] = $imp_1_tope_sac + $imp_1_tope;
                    }
                }

                $legajoActual['DifSACImponibleConOtroTope']   = 0;
                $legajoActual['DifImponibleConOtroTope']      = 0;
                if ($legajoActual['ImporteSACOtroAporte'] > $TopeSACJubilatorioOtroAp) {
                    if ($trunca_tope == 1) {
                        $legajoActual['DifSACImponibleConOtroTope'] = $legajoActual['ImporteSACOtroAporte'] - $TopeSACJubilatorioOtroAp;
                        $legajoActual['ImporteImponible_4']        -= $legajoActual['DifSACImponibleConOtroTope'];
                        $legajoActual['ImporteSACOtroAporte']       = $TopeSACJubilatorioOtroAp;
                    }
                }
                $legajoActual['OtroImporteImponibleSinSAC'] = $legajoActual['ImporteImponible_4'] - $legajoActual['ImporteSACOtroAporte'];
                if ($legajoActual['OtroImporteImponibleSinSAC'] > $TopeOtrosAportesPersonales) {
                    if ($trunca_tope == 1) {
                        $legajoActual['DifImponibleConOtroTope'] = $legajoActual['OtroImporteImponibleSinSAC'] - $TopeOtrosAportesPersonales;
                        $legajoActual['ImporteImponible_4']     -= $legajoActual['DifImponibleConOtroTope'];
                    }
                }
                if ($legajoActual['ImporteImponible_6'] != 0 && $legajoActual['TipoDeOperacion'] == 1) {
                    $legajoActual['IMPORTE_IMPON'] = 0;
                }
                // Calcular Sueldo m�s Adicionales
                $legajoActual['ImporteSueldoMasAdicionales'] = $legajoActual['ImporteImponiblePatronal'] -
                    $legajoActual['ImporteSAC'] -
                    $legajoActual['ImporteHorasExtras'] -
                    $legajoActual['ImporteZonaDesfavorable'] -
                    $legajoActual['ImporteVacaciones'] -
                    $legajoActual['ImportePremios'] -
                    $legajoActual['ImporteAdicionales'];
                if ($legajoActual['ImporteSueldoMasAdicionales'] > 0) {
                    $legajoActual['ImporteSueldoMasAdicionales'] -= $legajoActual['IncrementoSolidario'];
                }

                if (is_null($legajoActual['trabajadorconvencionado'])) {
                    $legajoActual['trabajadorconvencionado'] = self::$trabajadorConvencionado;
                }

                // Sumariza las asiganciones familiares en el bruto y deja las asiganciones familiares en cero, esto si en configuracion esta chequeado
                if (self::$asignacion_familiar) {
                    $legajoActual['IMPORTE_BRUTO'] += $legajoActual['AsignacionesFliaresPagadas'];
                    $legajoActual['AsignacionesFliaresPagadas'] = 0;
                }

                // Por ticket #3947. Check "Generar ART con tope"
                if (MapucheConfig::getParametroRrhh('Sicoss', 'ARTconTope', '1') === '0') // Sin tope
                {
                    $legajoActual['importeimponible_9'] = $legajoActual['Remuner78805'];
                } else // Con tope
                {
                    $legajoActual['importeimponible_9'] = $legajoActual['ImporteImponible_4'];
                }

                // Por ticket #3947. Check "Considerar conceptos no remunerativos en c�lculo de ART?"
                if (MapucheConfig::getParametroRrhh('Sicoss', 'ConceptosNoRemuEnART', '0') === '1') // Considerar conceptos no remunerativos
                {
                    $legajoActual['importeimponible_9'] += $legajoActual['ImporteNoRemun'];
                }

                // por GDS #5913 Incorporaci�n de conceptos no remunerativos a las remuneraciones 4 y 8 de SICOSS
                $legajoActual['Remuner78805'] += $legajoActual['NoRemun4y8'];
                $legajoActual['ImporteImponible_5'] = $legajoActual['ImporteImponible_4'];
                $legajoActual['ImporteImponible_4'] += $legajoActual['NoRemun4y8'];
                $legajoActual['ImporteImponible_4'] += $legajoActual['ImporteTipo91'];

                $legajoActual['IMPORTE_BRUTO'] += $legajoActual['ImporteNoRemun96'];
                $total['bruto']       += round($legajoActual['IMPORTE_BRUTO'], 2);
                $total['imponible_1'] += round($legajoActual['IMPORTE_IMPON'], 2);
                $total['imponible_2'] += round($legajoActual['ImporteImponiblePatronal'], 2);
                $total['imponible_4'] += round($legajoActual['ImporteImponible_4'], 2);
                $total['imponible_5'] += round($legajoActual['ImporteImponible_5'], 2);
                $total['imponible_8'] += round($legajoActual['Remuner78805'], 2);
                $total['imponible_6'] += round($legajoActual['ImporteImponible_6'], 2);
                $total['imponible_9'] += round($legajoActual['importeimponible_9'], 2);

                $legajos_validos[$j] = $legajoActual;
                $j++;
            } // fin else que verifica que los importes sean distintos de 0
            // Si los importes son cero el legajo no se agrega al archivo sicoss; pero cuando tengo el check de licencias por interface y ademas el legajo tiene licencias entonces si va
            elseif ($datos['check_lic'] && ($legajoActual['licencia'] == 1)) {
                // Inicializo variables faltantes en cero
                $legajoActual['ImporteSueldoMasAdicionales'] = 0;
                if (is_null($legajoActual['trabajadorconvencionado'])) {
                    $legajoActual['trabajadorconvencionado'] = self::$trabajadorConvencionado;
                }

                if ($datos['seguro_vida_patronal'] == 1 && $datos['check_lic'] == 1) {
                    $legajoActual['SeguroVidaObligatorio'] = 1;
                }
                $legajos_validos[$j] = $legajoActual;
                $j++;
            } elseif ($check_sin_activo && $legajoActual['codigosituacion'] == 14) {
                $legajos_validos[$j] = $legajoActual;
                $j++;
            }
        }

        if (!empty($legajos_validos)) {
            if ($retornar_datos === TRUE)
                return $legajos_validos;
            self::grabar_en_txt($legajos_validos, $nombre_arch);
        }

        return $total;
    }


    // Dado un arreglo, doy formato y agrego a archivo
    static function grabar_en_txt($legajos, $nombre_arch)
    {
        //Para todos los datos obtenidos habra q calcular lo que no esta en la consulta
        $archivo = storage_path('app/comunicacion/sicoss/' . $nombre_arch . '.txt');
        $fh = fopen($archivo, 'w') or die("Error!!");
        $total_legajos = count($legajos);
        // Proceso la tabla, le agrego las longitudes correpondientes
        for ($i = 0; $i < $total_legajos; $i++) {
            $legajoActual = &$legajos[$i];
            fwrite(
                $fh,
                $legajoActual['cuit'] .                                                                // Campo 1
                    self::llena_blancos_mod($legajoActual['apyno'], 30) .                                             // Campo 2
                    $legajoActual['conyugue'] .                                                                        // Campo 3
                    self::llena_importes($legajoActual['hijos'], 2) .                                                 // Campo 4
                    self::llena_importes($legajoActual['codigosituacion'], 2) .                                       // Campo 5 TODO: Preguntar �es el que viene de dha8?
                    self::llena_importes($legajoActual['codigocondicion'], 2) .                                       // Campo 6
                    self::llena_importes($legajoActual['TipoDeActividad'], 3) .                                       // Campo 7 - Segun prioridad es codigoactividad de dha8 u otro valor, ver funcion sumarizar_conceptos_por_tipos_grupos
                    self::llena_importes($legajoActual['codigozona'], 2) .                                            // Campo 8
                    self::llena_blancos_izq(number_format($legajoActual['aporteadicional'] ?? 0.0, 2, ',', ''), 5) .            // Campo 9 - Porcentaje de Aporte Adicional Obra Social
                    self::llena_importes($legajoActual['codigocontratacion'], 3) .                                    // Campo 10
                    self::llena_importes($legajoActual['codigo_os'], 6) .
                    self::llena_importes($legajoActual['adherentes'], 2) .                                            // Campo 12 - Seg�n este chequeado en configuraci�n informo 0 o uno (sumarizar_conceptos_por_tipos_grupos) o cantidad de adherentes (dh09)
                    self::llena_blancos_izq(number_format($legajoActual['IMPORTE_BRUTO'] ?? 0.0, 2, ',', ''), 12) .             // Campo 13
                    self::llena_blancos_izq(number_format($legajoActual['IMPORTE_IMPON'] ?? 0.0, 2, ',', ''), 12) .             // Campo 14
                    self::llena_blancos_izq(number_format($legajoActual['AsignacionesFliaresPagadas'] ?? 0.0, 2, ',', ''), 9) . // Campo 15
                    self::llena_blancos_izq(number_format($legajoActual['IMPORTE_VOLUN'] ?? 0.0, 2, ',', ''), 9) .              // Campo 16
                    self::llena_blancos_izq(number_format($legajoActual['IMPORTE_ADICI'] ?? 0.0, 2, ',', ''), 9) .              // Campo 17
                    self::llena_blancos_izq(number_format(abs($legajoActual['ImporteSICOSSDec56119'] ?? 0.0), 2, ',', ''), 9) .      //exedAportesSS
                    '     0,00' .                                                                                     //exedAportesOS
                    self::llena_blancos($legajoActual['provincialocalidad'], 50) .                                    //Campo 20
                    self::llena_blancos_izq(number_format($legajoActual['ImporteImponiblePatronal'] ?? 0.0, 2, ',', ''), 12) .  // Campo 21 - Imponible 2
                    self::llena_blancos_izq(number_format($legajoActual['ImporteImponiblePatronal'] ?? 0.0, 2, ',', ''), 12) .  // Campo 22 - Imponible 3
                    self::llena_blancos_izq(number_format($legajoActual['ImporteImponible_4'] ?? 0.0, 2, ',', ''), 12) .        // Campo 23 - Imponible 4
                    '00' .                                                                                            // campo 24 - codigo siniestrado
                    '0' .                                                                                             // Campo 25 - marca de corresponde reduccion
                    '000000,00' .                                                                                     // Campo 26 -  capital de recomposicion
                    self::$tipoEmpresa .
                    self::llena_blancos_izq(number_format($legajoActual['AporteAdicionalObraSocial'] ?? 0.0, 2, ',', ''), 9) .                                                                                     // Campo 28 - aporte adicional obra social
                    $legajoActual['regimen'] .
                    self::llena_importes($legajoActual['codigorevista1'], 2) .                                       // campo 30 - codigo de revista 1 se informa igual que codigosituacion
                    self::llena_importes($legajoActual['fecharevista1'], 2) .                                        // campo 31 - Dia inicio Situaci�n de Revista 1
                    self::llena_importes($legajoActual['codigorevista2'], 2) .                                       // Situaci�n de Revista 2
                    self::llena_importes($legajoActual['fecharevista2'], 2) .                                        // Dia inicio Situaci�n de Revista 2
                    self::llena_importes($legajoActual['codigorevista3'], 2) .                                       // Situaci�n de Revista 3
                    self::llena_importes($legajoActual['fecharevista3'], 2) .                                        // Dia inicio Situaci�n de Revista 3
                    self::llena_blancos_izq(number_format($legajoActual['ImporteSueldoMasAdicionales'] ?? 0.0, 2, ',', ''), 12) .        // Campo 36
                    self::llena_blancos_izq(number_format($legajoActual['ImporteSAC'] ?? 0.0, 2, ',', ''), 12) .                // Campo 37
                    self::llena_blancos_izq(number_format($legajoActual['ImporteHorasExtras'] ?? 0.0, 2, ',', ''), 12) .        // Campo 38
                    self::llena_blancos_izq(number_format($legajoActual['ImporteZonaDesfavorable'] ?? 0.0, 2, ',', ''), 12) .   // Campo 39
                    self::llena_blancos_izq(number_format($legajoActual['ImporteVacaciones'] ?? 0.0, 2, ',', ''), 12) .         // Campo 40
                    '0000000' . self::llena_importes($legajoActual['dias_trabajados'], 2) .                            // Campo 41 - D�as trabajados
                    self::llena_blancos_izq(number_format($legajoActual['ImporteImponible_4'] - $legajoActual['ImporteTipo91'], 2, ',', ''), 12) .        // Campo 42 - Imponible5 = Imponible4 - ImporteTipo91
                    $legajoActual['trabajadorconvencionado'] .
                    self::llena_blancos_izq(number_format($legajoActual['ImporteImponible_6'] ?? 0.0, 2, ',', ''), 12) .        // Campo 44 - Imponible 6
                    $legajoActual['TipoDeOperacion'] .                                                                 // Campo 45 - Segun se redondee o no importe imponible 6
                    self::llena_blancos_izq(number_format($legajoActual['ImporteAdicionales'] ?? 0.0, 2, ',', ''), 12) .        // Campo 46
                    self::llena_blancos_izq(number_format($legajoActual['ImportePremios'] ?? 0.0, 2, ',', ''), 12) .            // Campo 47
                    self::llena_blancos_izq(number_format($legajoActual['Remuner78805'] ?? 0.0, 2, ',', ''), 12) .              // Campo 48
                    self::llena_blancos_izq(number_format($legajoActual['ImporteImponible_6'] ?? 0.0, 2, ',', ''), 12) .        // Campo 49 - Imponible7 = Imponible6
                    //redondeo las HS extras, si vienen por ejemplo 10.5 en sicoss informo 11. Esto es porque el
                    //campo de sicoss es de 3 caracteres y los 10.5 los informaria como 0.5
                    self::llena_importes(ceil($legajoActual['CantidadHorasExtras']), 3) .                                   // Campo 50
                    self::llena_blancos_izq(number_format($legajoActual['ImporteNoRemun'] ?? 0.0, 2, ',', ''), 12) .            // Campo 51
                    self::llena_blancos_izq(number_format($legajoActual['ImporteMaternidad'] ?? 0.0, 2, ',', ''), 12) .         // Campo 52
                    self::llena_blancos_izq(number_format($legajoActual['ImporteRectificacionRemun'] ?? 0.0, 2, ',', ''), 9) .  // Campo 53
                    self::llena_blancos_izq(number_format($legajoActual['importeimponible_9'] ?? 0.0, 2, ',', ''), 12) .        // Campo 54 = Imponible8 (Campo 48) + Conceptos No remunerativos (Campo 51)
                    self::llena_blancos_izq(number_format($legajoActual['ContribTareaDif'] ?? 0.0, 2, ',', ''), 9) .            // Campo 55 - Contribuci�n Tarea Diferencial
                    '000' .                                                                                             // Campo 56 - Horas Trabajadas
                    $legajoActual['SeguroVidaObligatorio'] .                                                           // Campo 57 - Seguro  de Vida Obligatorio
                    self::llena_blancos_izq(number_format($legajoActual['ImporteSICOSS27430'] ?? 0.0, 2, ',', ''), 12) .         // Campo 58 - Importe a detraer Ley 27430
                    self::llena_blancos_izq(number_format($legajoActual['IncrementoSolidario'] ?? 0.0, 2, ',', ''), 12) . // Campo 59 - Incremento Solidario para empresas del sector privado y p�blico (D. 14/2020 y 56/2020)
                    self::llena_blancos_izq(number_format(0, 2, ',', ''), 12) .                                          // Campo 60 - Remuneraci�n 11
                    "\r\n"
            );
        }
        fclose($fh);
    }

    // Similar a VerificarConceptosRemuneratorios en pampa.
    // Dado un legajo hace la sumarizaci�n de conceptos liquidados seg�n corresponda:
    // por tipo de grupo al que pertenece un concepto o codigo de concepto
    static function sumarizar_conceptos_por_tipos_grupos($nro_leg, &$leg)
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

        $conceptos_liq_por_leg = self::consultar_conceptos_liquidados($nro_leg, 'true');

        // Sumarizo donde corresponda para cada concepto liquidado
        // Cuando recorro guardo el numero de cargo si es investigador, para luego procesar en calcularSACInvestigador
        $conce_hs_extr = [];
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
                    $horas = self::calculo_horas_extras($codn_concepto, $nro_cargo);
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

        $leg['SACInvestigador'] = self::calcularSACInvestigador($nro_leg, $cargoInvestigador);
    }

    // Recibo un arreglo con los cargos que son investigadores, para cada uno calculo el sac investigador y sumarizo. Se tiene en cuenta los de tipo 9
    static function calcularSACInvestigador($nro_leg, $cargos)
    {
        $sacInvestigador = 0;
        $cargos = array_unique($cargos); // limpio cargos duplicados
        foreach ($cargos as $cargo) {
            $where = " nro_cargo = $cargo
                       --filtro solo los que tienen tipo de concepto = 9 como es una lista uso exp. reg.
                       AND array_to_string(tipos_grupos,',') ~ '(:?^|,)+9(:?$|,)'";
            $conceptos_liq_por_leg = self::consultar_conceptos_liquidados($nro_leg, $where);
            for ($j = 0; $j < count($conceptos_liq_por_leg); $j++) {
                $sacInvestigador += $conceptos_liq_por_leg[$j]['impp_conce'];
            }
        }

        return $sacInvestigador;
    }


    // Obtengo los conceptos liquidados para el legajo, su importe y los tipos de grupo asociados al concepto
    static function consultar_conceptos_liquidados($nro_leg, $where)
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

        $conceptos_filtrados = DB::connection(self::getStaticConnectionName())->select($sql_conceptos_fltrados);

        return $conceptos_filtrados;
    }

    static function calculo_horas_extras($concepto, $cargo)
    {
        $sql = "	SELECT
						cargo,concepto,sum(nov1) as sum_nov1
					FROM (
							SELECT
								dh21.nro_cargo,dh21.codn_conce,	sum(t.nov1_conce)/(SELECT
	    																				COUNT(*)
	    										    								FROM
	    																				 mapuche.dh24
	    										    								WHERE
	    										   										nro_cargo=$cargo
	    																			) as horas, t.id_liquidacion
							FROM
					 			pre_conceptos_liquidados t
	    					LEFT JOIN
	    							 mapuche.dh21 dh21 ON (t.id_liquidacion=dh21.id_liquidacion)
							GROUP BY
									dh21.nro_cargo,dh21.codn_conce,dh21.nro_liqui, dh21.nro_legaj,
									dh21.codn_progr, dh21.codn_subpr,dh21.codn_proye,dh21.codn_activ,dh21.codn_obra,dh21.codn_fuent,
									dh21.codn_area,dh21.codn_subar,dh21.codn_final,dh21.codn_funci,dh21.codn_grupo_presup,dh21.tipo_ejercicio,
									dh21.codn_subsubar, t.id_liquidacion
							) t1(cargo,concepto,nov1)
					WHERE
						cargo=$cargo AND concepto=$concepto
					GROUP BY
						cargo,concepto
					ORDER BY
    					cargo,concepto";
        $horas = DB::connection(self::getStaticConnectionName())->select($sql);
        return $horas[0];
    }
    // Sumariza importes de conceptos que pertenecen a un determinado tipo de concepto
    // Se podia hacer en la funcion sumarizar_conceptos_por_tipos_grupos pero queda mas claro separado
    static function calcular_remuner_grupo($nro_legajo, $tipo, $where)
    {
        $sql =
            "
				SELECT
					SUM(conceptos_liquidados.impp_conce::numeric(10,2)) AS suma
				FROM
					conceptos_liquidados
				WHERE
					nro_legaj = $nro_legajo
					AND tipo_conce = '$tipo'
					AND $where";

        $suma = DB::connection(self::getStaticConnectionName())->select($sql);
        return (float)$suma[0]['suma'];
    }

    // Se obtienen los importes de otra actividad, cuando tiene varias tomo la del �ltimo periodo
    static function otra_actividad($nro_legajo)
    {
        $sql = "
				SELECT
					importe AS ImporteBrutoOtraActividad,
					importe_sac AS ImporteSACOtraActividad
				FROM
					mapuche.dhe9
				WHERE
					nro_legaj = $nro_legajo
				ORDER BY
					vig_ano, vig_mes DESC
				LIMIT 1
			";
        $resp = DB::connection(self::getStaticConnectionName())->select($sql);
        if (empty($resp))
            $resp[0] = array('importesacotraactividad' => 0, 'importebrutootraactividad' => 0);
        return $resp[0];
    }

    // Verifica los importes dado un legajo, si todos son ceros entonces no debe tenerse en cuenta en el informe sicoss
    static function VerificarAgenteImportesCERO($leg)
    {
        $VerificarAgenteImportesCERO = 1;
        if ($leg['IMPORTE_BRUTO'] == 0 && $leg['IMPORTE_IMPON'] == 0 && $leg['AsignacionesFliaresPagadas'] == 0 && $leg['ImporteNoRemun'] == 0 && $leg['IMPORTE_ADICI'] == 0 && $leg['IMPORTE_VOLUN'] == 0)
            $VerificarAgenteImportesCERO = 0;
        return $VerificarAgenteImportesCERO;
    }

    // Devuelve el c�digo dgi de obra social correspondiente dado un legajo
    static function codigo_os($nro_legajo)
    {
        // Si es jubilado directamente retorno 000000.
        // Si no es jubilado me fijo en el campo de dh09
        // Si campo dh09 no esta vacia con ese codigo obtengo el codigo dgi
        // Si campo dh09 esta vacio asigno la obra social por defecto y luego obtengo codigo dgi
        // Si no existe codigo dgi devuelvo 000000

        if (Dh01::esJubilado($nro_legajo))
            return '000000';

        $sql = "
				SELECT
					dh09.codc_obsoc
				FROM
					mapuche.dh09
				WHERE
					dh09.nro_legaj = $nro_legajo
			";
        $siglas = DB::connection(self::getStaticConnectionName())->select($sql);

        if (empty($siglas[0]['codc_obsoc'])) {
            $siglas[0]['codc_obsoc'] = self::$codigo_obra_social_default;
        }

        $sigla = self::quote($siglas[0]['codc_obsoc']);

        $sql2 = "
				SELECT
					dh37.codn_osdgi
				FROM
					mapuche.dh37
				WHERE
					dh37.codc_obsoc = $sigla
				";
        $coddgi = DB::connection(self::getStaticConnectionName())->select($sql2);

        if (empty($coddgi[0]['codn_osdgi']))
            $coddgi[0]['codn_osdgi'] = '000000';
        return $coddgi[0]['codn_osdgi'];
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

    static function armar_zip()
    {
        //-- Generar el archivo ZIP para descargarlo

        $nombre = 'sicoss';
        $path   = storage_path('app/comunicacion/sicoss/');

        $archivo = $path . $nombre . '.zip';
        // Si existe lo elimino
        if (file_exists($archivo))
            unlink($archivo);

        $archivos_adjuntados = 0;

        if (isset(self::$archivos)) {
            // $zip = new mapuche_zip($path, $nombre);

            // Adjunto archivos
            foreach (self::$archivos as $archivo) {
                $archivo_texto = $archivo . ".txt";
                if (file_exists($archivo_texto)) {
                    // $zip->agregar_archivo($archivo_texto);
                    $archivos_adjuntados++;
                }
            }

            // $zip->cerrar();

            // Elimino archivos txt temporales
            foreach (self::$archivos as $archivo) {
                $archivo_texto = $archivo . '.txt';
                if (file_exists($archivo_texto))
                    unlink($archivo_texto);
            }
        }

        if ($archivos_adjuntados == 0 && file_exists($archivo))
            unlink($archivo);
    }

    /**
     * Escapa un valor o array de valores para uso en consultas SQL
     *
     * @param mixed $dato Valor o array a escapar
     * @return string|array
     */
    public static function quote(mixed $dato): string|array
    {
        if ($dato === null) {
            return 'NULL';
        }

        if (!is_array($dato)) {
            // Asumiendo que $this->conexion es una instancia de PDO
            return DB::connection()->getPdo()->quote($dato);
        }

        $salida = [];
        foreach (array_keys($dato) as $clave) {
            $salida[$clave] = self::quote($dato[$clave]);
        }
        return $salida;
    }

    /**
     * Retorna el nombre de la conexión estática.
     *
     * Esta función devuelve el nombre de la conexión estática de la instancia actual.
     *
     * @return string El nombre de la conexión estática.
     */
    public static function getStaticConnectionName()
    {
        $instance = new static();
        return $instance->getConnectionName();
    }
}
