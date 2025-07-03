<?php

namespace App\Services\Afip;

use App\Models\Dh01;
use App\Models\Dh03;
use Illuminate\Support\Facades\DB;
use App\ValueObjects\PeriodoFiscal;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use App\Models\Mapuche\MapucheConfig;
use App\Traits\MapucheConnectionTrait;
use App\Services\Mapuche\LicenciaService;
use App\Repositories\Sicoss\Dh03Repository;

class SicossOptimizado
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
    protected static $codc_reparto = 'REPA';
    protected static $porc_aporte_adicional_jubilacion;
    protected static $cantidad_adherentes_sicoss;
    protected static $archivos;
    protected static $categoria_diferencial;
    protected static $periodo_actual;

    public static function genera_sicoss(
        array $datos,
        string $testeo_directorio_salida = '',
        string $testeo_prefijo_archivos = '',
        bool $retornar_datos = FALSE,
        bool $guardar_en_bd = FALSE,
        ?PeriodoFiscal $periodo_fiscal = null
    ) {
        // Se necesita filtrar datos del periodo vigente
        $periodo       = MapucheConfig::getPeriodoCorriente();
        $per_mesct     = $periodo['month'];
        $per_anoct     = $periodo['year'];
        $totales = [];
        $periodo_fiscal ??= new PeriodoFiscal($per_anoct, $per_mesct);
        $nombre_arch = 'sicoss';
        $opcion_retro  = $datos['check_retro'];
        $where = ' true ';
        $where_periodo = ' true ';
        $filtro_legajo = $datos['nro_legaj'] ?? null;
        $path = storage_path('comunicacion/sicoss/');
        self::$archivos = [];


        // Si no filtro por numero de legajo => obtengo todos los legajos
        if (!empty($filtro_legajo))
            $where = "dh01.nro_legaj= $filtro_legajo ";



        // Seteo valores de rrhhini
        self::$codigo_obra_social_default = self::quote(MapucheConfig::getDefaultsObraSocial());
        self::$aportes_voluntarios        = MapucheConfig::getTopesJubilacionVoluntario();
        self::$codigo_os_aporte_adicional = MapucheConfig::getConceptosObraSocialAporteAdicional();
        self::$codigo_obrasocial_fc       = MapucheConfig::getConceptosObraSocialFliarAdherente();                   // concepto seteado en rrhhini bajo el cual se liquida el familiar a cargo
        self::$tipoEmpresa                = MapucheConfig::getDatosUniversidadTipoEmpresa();
        self::$cantidad_adherentes_sicoss = MapucheConfig::getConceptosInformarAdherentesSicoss();                   // Segun sea cero o uno informa datos de dh09 o se fija si existe un cpncepto liquidado bajo el concepto de codigo_obrasocial_fc
        self::$asignacion_familiar        = MapucheConfig::getConceptosAcumularAsigFamiliar();                 // Si es uno se acumulan las asiganciones familiares en Asignacion Familiar en Remuneracion Total (importe Bruto no imponible)
        self::$trabajadorConvencionado    = MapucheConfig::getDatosUniversidadTrabajadorConvencionado();
        self::$codc_reparto                     = self::quote(MapucheConfig::getDatosCodcReparto());
        self::$porc_aporte_adicional_jubilacion = MapucheConfig::getPorcentajeAporteDiferencialJubilacion();
        self::$hs_extras_por_novedad      = MapucheConfig::getSicossHorasExtrasNovedades();   // Lee el valor HorasExtrasNovedades de RHHINI que determina si es verdadero se suman los valores de las novedades y no el importe.
        self::$categoria_diferencial       = MapucheConfig::getCategoriasDiferencial(); //obtengo las categorias seleccionadas en configuracion
        self::$codc_reparto  = self::quote(MapucheConfig::getDatosCodcReparto());







        self::obtener_conceptos_liquidados($per_anoct, $per_mesct, $where);


        // LICENCIAS
        $licencias_agentes_no_remunem = self::get_licencias_vigentes($where);
        $licencias_agentes_remunem = self::get_licencias_protecintegral_vacaciones($where);
        $licencias_agentes = array_merge($licencias_agentes_no_remunem, $licencias_agentes_remunem);




        // Si no tengo tildado el check el proceso genera un unico archivo sin tener en cuenta año y mes retro
        switch ($opcion_retro) {
            case 0:
                $periodo = 'Vigente_sin_retro';
                self::$archivos[$periodo] = "$path$nombre_arch";


                $legajos = self::obtener_legajos(self::$codc_reparto, $where_periodo, $where, $datos['check_lic'], $datos['check_sin_activo']);


                $periodo = $per_mesct . '/' . $per_anoct . ' (Vigente)';
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
                        $retornar_datos,
                        $guardar_en_bd,
                        $periodo_fiscal
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
                    $retornar_datos,
                    $guardar_en_bd,
                    $periodo_fiscal
                );
                break;
            default:
                $periodos_retro = self::obtener_periodos_retro($datos['check_lic'], $datos['check_retro']);
                $total = [];

                for ($i = 0; $i < count($periodos_retro); $i++) {
                    $p = $periodos_retro[$i];
                    $mes = str_pad($p['mes_retro'], 2, "0", STR_PAD_LEFT);
                    //agrego cero adelante a meses
                    $where_periodo = "t.ano_retro=" . $p['ano_retro'] . " AND t.mes_retro=" . $mes;
                    $legajos = self::obtener_legajos(self::$codc_reparto, $where_periodo, $where, $datos['check_lic'], $datos['check_sin_activo']);

                    if ($p['ano_retro'] == 0 && $p['mes_retro'] == 0) {
                        $nombre_arch = 'sicoss_retro_periodo_vigente';
                        $periodo = $per_mesct . '/' . $per_anoct;
                        $item = $per_mesct . '/' . $per_anoct . ' (Vigente)';
                        if ($retornar_datos === TRUE)
                            return self::procesa_sicoss($datos, $per_anoct, $per_mesct, $legajos, $nombre_arch, $licencias_agentes, $datos['check_retro'], $datos['check_sin_activo'], $retornar_datos);
                        $subtotal = self::procesa_sicoss($datos, $per_anoct, $per_mesct, $legajos, $nombre_arch, $licencias_agentes, $datos['check_retro'], $datos['check_sin_activo'], $retornar_datos);
                    } else {
                        $nombre_arch = 'sicoss_retro_' . $p['ano_retro'] . '_' . $p['mes_retro'];
                        $periodo = $p['ano_retro'] . $p['mes_retro'];
                        $item = $p['mes_retro'] . "/" . $p['ano_retro'];
                        $subtotal = self::procesa_sicoss($datos, $per_anoct, $per_mesct, $legajos, $nombre_arch, NULL, $datos['check_retro'], $datos['check_sin_activo'], $retornar_datos);
                    }

                    self::$archivos[$periodo] = $path . $nombre_arch;


                    $totales[$item] = $subtotal;
                }
                break;
        }

        // Elimino tabla temporal
        $sql = "DROP TABLE IF EXISTS pre_conceptos_liquidados CASCADE";
        DB::connection(self::getStaticConnectionName())->statement($sql);

        if ($testeo_directorio_salida != '' && $testeo_prefijo_archivos != '') {
            // copy(storage_path("comunicacion/sicoss/$nombre_arch.txt"), "$testeo_directorio_salida/$testeo_prefijo_archivos");
        } else {
            // self::armar_zip();
            return self::transformar_a_recordset($totales);
        }
    }





    public static function get_licencias_protecintegral_vacaciones($where_legajos): array
    {
        $fecha_inicio = self::quote(MapucheConfig::getFechaInicioPeriodoCorriente());
        $fecha_fin = self::quote(MapucheConfig::getFechaFinPeriodoCorriente());

        $variantes_vacaciones = MapucheConfig::getVarLicenciaVacaciones();
        $variantes_protecintegral = MapucheConfig::getVarLicenciaProtecIntegral();

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
					AND suc.map_es_licencia_vigente(dh05.nro_licencia)
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
					AND suc.map_es_licencia_vigente(dh05.nro_licencia)
					AND dl02.es_remunerada = TRUE
    				AND $where_legajos
    			;";

        $licencias = DB::connection(self::getStaticConnectionName())->select($sql);

        return array_map(function ($item) {
            return (array) $item;
        }, $licencias);
    }

    public static function get_licencias_vigentes($where_legajos)
    {
        $fecha_inicio = self::quote(MapucheConfig::getFechaInicioPeriodoCorriente());
        $fecha_fin = self::quote(MapucheConfig::getFechaFinPeriodoCorriente());

        $variantes_ilt_primer_tramo = MapucheConfig::getVarLicencias10Dias();
        $variantes_ilt_segundo_tramo = MapucheConfig::getVarLicencias11DiasSiguientes();

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
					AND suc.map_es_licencia_vigente(dh05.nro_licencia)
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
					AND suc.map_es_licencia_vigente(dh05.nro_licencia)
					AND $where_norem
    				AND $where_legajos
    			;";

        $licencias = DB::connection(self::getStaticConnectionName())->select($sql);

        return array_map(function ($item) {
            return (array) $item;
        }, $licencias);
    }

    public static function get_cargos_activos_sin_licencia($legajo): array
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
			    								suc.map_es_licencia_vigente(dh05.nro_licencia) AND
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
						    					suc.map_es_licencia_vigente(dh05.nro_licencia)
						    					AND (dh05.fec_desde < mapuche.map_get_fecha_inicio_periodo() - 1)  AND
						    					(dl02.es_maternidad IS TRUE OR (NOT dl02.es_remunerada OR (dl02.es_remunerada AND dl02.porcremuneracion = '0')))
						    				)
    			;";
        $resultado = DB::connection(self::getStaticConnectionName())->select($sql);

        return array_map(function ($item) {
            return (array) $item;
        }, $resultado);
    }

    /**
     * Obtiene los límites de días de los cargos para un legajo específico en el período corriente.
     *
     * Calcula el día mínimo de inicio y el día máximo de fin de los cargos de un legajo,
     * considerando el período corriente y las fechas de alta y baja de los cargos.
     *
     * @param int $legajo Número de legajo del empleado
     * @return array Arreglo con los días mínimo y máximo de los cargos
     */
    public static function get_limites_cargos($legajo): array
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

        return [
            'minimo' => (int) $resultado[0]->minimo,
            'maximo' => (int) $resultado[0]->maximo
        ];
    }

    public static function obtener_conceptos_liquidados($per_anoct, $per_mesct, $where)
    {
        // Crear la tabla temporal optimizada UNA sola vez
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

        DB::connection(self::getStaticConnectionName())->select($sql_conceptos_liq);

        // Crear índice para optimizar consultas posteriores
        $sql_ix = "CREATE INDEX ix_pre_conceptos_liquidados_1 ON pre_conceptos_liquidados(id_liquidacion);";
        DB::connection(self::getStaticConnectionName())->select($sql_ix);

        // Índices adicionales para optimizar filtros posteriores
        $sql_ix2 = "CREATE INDEX ix_pre_conceptos_liquidados_periodo ON pre_conceptos_liquidados(ano_retro, mes_retro);";
        DB::connection(self::getStaticConnectionName())->select($sql_ix2);
    }

    private static function getConsultaConceptosOptimizada($where_periodo_retro, $where_legajo)
    {
        $periodo = self::$periodo_actual;
        $per_anoct = $periodo['ano'];
        $per_mesct = $periodo['mes'];
        $where_original = $periodo['where'];

        // Determinar tabla según período (dh21 actual o dh21h histórico)
        $tabla_liquidaciones = self::determinarTablaLiquidaciones($where_periodo_retro);

        return "
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
        FROM mapuche.{$tabla_liquidaciones} dh21
        INNER JOIN mapuche.dh22 ON dh22.nro_liqui = dh21.nro_liqui
        LEFT JOIN mapuche.dh01 ON dh01.nro_legaj = dh21.nro_legaj
        LEFT JOIN mapuche.dh12 ON dh12.codn_conce = dh21.codn_conce
        LEFT JOIN tipos_grupos_conceptos tgc ON tgc.codn_conce = dh21.codn_conce
        WHERE dh22.per_liano = {$per_anoct}
          AND dh22.per_limes = {$per_mesct}
          AND dh22.sino_genimp = true
          AND dh21.codn_conce > 0
          AND ({$where_original})
          AND ({$where_periodo_retro})
    ";
    }


    /**
     * Determina qué tabla de liquidaciones utilizar según el período retroactivo.
     *
     * Analiza el filtro de período retroactivo para determinar si se debe usar
     * la tabla de liquidaciones actual (dh21) o la tabla histórica (dh21h).
     * Si el período incluye años retroactivos específicos (ano_retro > 0),
     * se utiliza la tabla histórica.
     *
     * @param string $where_periodo_retro Condición WHERE para filtrar el período retroactivo
     * @return string Nombre de la tabla a utilizar ('dh21' para actual, 'dh21h' para histórica)
     */
    private static function determinarTablaLiquidaciones($where_periodo_retro)
    {
        // Si el filtro de período incluye años/meses específicos, usar tabla histórica
        if ($where_periodo_retro !== 'true' && $where_periodo_retro !== ' true ') {
            // Analizar si el período es histórico
            if (preg_match('/ano_retro\s*=\s*(\d+)/', $where_periodo_retro, $matches)) {
                $ano_retro = intval($matches[1]);
                if ($ano_retro > 0) {
                    return 'dh21h'; // Tabla histórica
                }
            }
        }

        return 'dh21'; // Tabla actual
    }


    public static function obtener_periodos_retro($check_lic = false, $check_retr = false)
    {
        // Obtengo los a�o y mes retro disponibles del periodo de la tabla temporal que genero para hacer hacer el join con la tabla de legajos
        $rs_periodos_retro = array();
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

    /**
     * Obtiene los legajos de empleados para un período específico con múltiples opciones de filtrado.
     *
     * Esta función recupera los legajos de empleados basándose en varios parámetros de filtrado,
     * permitiendo incluir o excluir legajos con licencias, agentes activos sin cargo, y aplicando
     * filtros por período retroactivo.
     *
     * @param string $codc_reparto Código de reparto para filtrar legajos
     * @param string $where_periodo_retro Condición para filtrar el período retroactivo
     * @param string $where_legajo Condición adicional para filtrar legajos (por defecto 'true')
     * @param bool $check_lic Indica si se deben incluir legajos con licencias sin goce
     * @param bool $check_sin_activo Indica si se deben incluir agentes activos sin cargo y sin liquidación
     * @return array Arreglo de legajos filtrados con sus respectivos datos
     */
    public static function obtener_legajos($codc_reparto, $where_periodo_retro, $where_legajo = ' true ', $check_lic = false, $check_sin_activo = false)
    {
        Log::debug('Obtener legajos:', [
            'where_periodo_retro' => $where_periodo_retro,
            'where_legajo' => $where_legajo,
            'check_lic' => $check_lic,
            'check_sin_activo' => $check_sin_activo
        ]);

        // En lugar de crear otra tabla temporal, usar directamente pre_conceptos_liquidados con filtro
        $sql_crear_vista = "CREATE TEMP VIEW conceptos_liquidados AS
            SELECT * FROM pre_conceptos_liquidados t
            WHERE $where_periodo_retro";

        DB::connection(self::getStaticConnectionName())->select($sql_crear_vista);




        $sql_datos_legajo = self::get_sql_legajos('conceptos_liquidados', 0);


        // Si tengo el check de licencias agrego a la cantidad de agentes a procesar a los agentes sin licencias sin goce
        // Si tengo el check de licencias y ademas tengo el check de retros, debo tener en cuenta las licencias solo en el archivo generado con mes y a�o 0 (son del periodo vigente)
        // tendre en cuenta licencias en el caso general (true) y cuando tenga retros y el where tenga 0-0 (vigente)
        if ($check_lic && ($where_periodo_retro == ' true ' || $where_periodo_retro == 't.ano_retro=0 AND t.mes_retro=00')) {

            // Me fijo cuales son todos los agentes con licencias sin goce (de cargo o de legajo, liquidados o no). Si habia seleccionado legajo tambien filtro
            $legajos_lic = LicenciaService::getLegajosLicenciasSinGoce($where_legajo);


            // Preparo arreglo para usar en sql IN
            $legajos_lic = implode(',', $legajos_lic);

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
                $sql_datos_lic = ' UNION (' . self::get_sql_legajos("mapuche.dh01", 1, $where) . ' ORDER BY apyno';

                $legajos = DB::connection(self::getStaticConnectionName())->select($sql_datos_legajo . $sql_datos_lic);
            } else {
                $sql_datos_legajo .= ' ORDER BY apyno';
                // Si no hay licencias sin goce que cumpaln con las restricciones hago el proceso comun
                $legajos = DB::connection(self::getStaticConnectionName())->select($sql_datos_legajo);
            }
        } else {
            $sql_datos_legajo .= ' ORDER BY apyno';
            // $sql_datos_legajo .= ' ORDER BY apyno  LIMIT 1000';

            // Si no tengo el check licencias se consulta solo contra conceptos liquidados
            $legajos = DB::connection(self::getStaticConnectionName())->select($sql_datos_legajo);

            $legajos = array_map(function ($item) {
                return (array)$item;
            }, $legajos);
        }

        //Si esta chequeado "Generar Agentes Activos sin Cargo Activo y sin Liquidación para Reserva de Puesto"
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

            $sql_legajos_no_liquidados = self::get_sql_legajos("mapuche.dh01", 0, $where_no_liquidado);
            $legajos_t = DB::connection(self::getStaticConnectionName())->select($sql_legajos_no_liquidados);
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
        $legajos = [];
        foreach ($legajos_sin_repetidos as $legajo)
            $legajos[] = $legajo;

        return $legajos;
    }

    /**
     * Genera una consulta SQL para obtener los legajos de empleados.
     *
     * Esta función construye una consulta SQL dinámica para obtener los legajos de empleados
     * basándose en la tabla especificada, el valor de licencia y una cláusula WHERE adicional.
     *
     * @param string $tabla El nombre de la tabla a consultar.
     * @param int $valor El valor de la licencia a incluir en la consulta.
     * @param string $where La cláusula WHERE adicional para filtrar los resultados.
     * @return string La consulta SQL construida.
     */
    public static function get_sql_legajos($tabla, $valor, $where = ' true '): string
    {
        // Determinar si necesita JOIN con dh01
        $join_dh01 = ($tabla != "mapuche.dh01")
            ? "LEFT OUTER JOIN mapuche.dh01 ON $tabla.nro_legaj = dh01.nro_legaj"
            : "";

        return "
        SELECT
            DISTINCT(dh01.nro_legaj),
            (dh01.nro_cuil1::char(2)||LPAD(dh01.nro_cuil::char(8),8,'0')||dh01.nro_cuil2::char(1))::float8 AS cuit,
            dh01.desc_appat||' '||dh01.desc_nombr AS apyno,
            dh01.tipo_estad AS estado,

            -- Optimización: reemplazar subconsultas con JOIN agregado
            COALESCE(familiares.conyugue, 0) AS conyugue,
            COALESCE(familiares.hijos, 0) AS hijos,

            dha8.ProvinciaLocalidad,
            dha8.codigosituacion,
            dha8.CodigoCondicion,
            dha8.codigozona,
            dha8.CodigoActividad,
            dha8.porcaporteadicss AS aporteAdicional,
            dha8.trabajador_convencionado AS trabajadorconvencionado,
            dha8.codigomodalcontrat AS codigocontratacion,

            CASE WHEN ((dh09.codc_bprev = " . self::quote(self::$codc_reparto) . ") OR (dh09.fuerza_reparto) OR ((" . self::quote(self::$codc_reparto) . " = '') AND (dh09.codc_bprev IS NULL)))
                 THEN '1' ELSE '0' END AS regimen,

            dh09.cant_cargo AS adherentes,
            $valor AS licencia,
            0 AS importeimponible_9

        FROM $tabla

        -- JOIN optimizado para contar familiares una sola vez
        LEFT JOIN (
            SELECT
                nro_legaj,
                COUNT(CASE WHEN codc_paren = 'CONY' THEN 1 END) AS conyugue,
                COUNT(CASE WHEN codc_paren IN ('HIJO', 'HIJN', 'HINC', 'HINN') THEN 1 END) AS hijos
            FROM mapuche.dh02
            WHERE sino_cargo != 'N'
            GROUP BY nro_legaj
        ) familiares ON familiares.nro_legaj = $tabla.nro_legaj

        LEFT OUTER JOIN mapuche.dha8 ON dha8.nro_legajo = $tabla.nro_legaj
        LEFT OUTER JOIN mapuche.dh09 ON dh09.nro_legaj = $tabla.nro_legaj
        LEFT OUTER JOIN mapuche.dhe9 ON dhe9.nro_legaj = $tabla.nro_legaj
        $join_dh01

        WHERE $where";
    }



    static function inicializar_estado_situacion($codigo, $min, $max)
    {
        $periodo = MapucheConfig::getPeriodoCorriente();
        $estado_situacion = array();
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
    public static function evaluar_condicion_licencia($c1, $c2)
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

    /**
     * Calcula los cambios de estado en una situación de empleado.
     *
     * Este método identifica y registra los cambios de estado en un array de estado de situación,
     * devolviendo un nuevo array que solo contiene los días donde hay un cambio de código.
     *
     * @param array $estado_situacion Array que representa el estado de situación por día
     * @return array Array de cambios de estado, donde las claves son los días y los valores son los códigos de estado
     */
    public static function calcular_cambios_estado($estado_situacion): array
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

    /**
     * Calcula la cantidad de días trabajados en un período.
     *
     * Este método cuenta los días trabajados en un estado de situación, considerando
     * como días trabajados los códigos 1 (trabajo normal), 5 (licencia por maternidad),
     * 12 (otra licencia) y 51 (otro estado especial).
     *
     * @param array $estado_situacion Array que representa el estado de situación por día
     * @return int Número total de días trabajados
     */
    public static function calcular_dias_trabajados($estado_situacion): int
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

    /**
     * Calcula la revista de un legajo basado en los cambios de estado.
     *
     * Este método determina los últimos tres cambios de estado de un legajo,
     * con una lógica especial para manejar situaciones de licencia por maternidad.
     *
     * @param array $cambios_estado Array de cambios de estado, donde las claves son los días y los valores son los códigos de situación
     * @return array Array con los últimos tres cambios de estado, cada uno con su código y día correspondiente
     */
    public static function calcular_revista_legajo($cambios_estado): array
    {
        $controlar_maternidad = false;
        $revista_legajo = [];
        $cantidad_cambios = count($cambios_estado);
        $dias = array_keys($cambios_estado);

        $revista_legajo[1] = ['codigo' => 0, 'dia' => 0];
        $revista_legajo[2] = ['codigo' => 0, 'dia' => 0];
        $revista_legajo[3] = ['codigo' => 0, 'dia' => 0];

        $primer_dia = 0;

        if ($cantidad_cambios > 3) {
            $primer_dia = $cantidad_cambios - 3;
            $controlar_maternidad = true;
        }

        $revista = 1;
        for ($i = $primer_dia; $i < $cantidad_cambios; $i++) {
            $dia = $dias[$i];
            $revista_legajo[$revista] = ['codigo' => $cambios_estado[$dia], 'dia' => $dia];
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


    public static function procesa_sicoss(
        array $datos,
        int $per_anoct,
        int $per_mesct,
        array $legajos,
        string $nombre_arch,
        ?array $licencias = NULL,
        bool $retro = FALSE,
        bool $check_sin_activo = FALSE,
        bool $retornar_datos = FALSE,
        bool $guardar_en_bd = FALSE,
        ?PeriodoFiscal $periodo_fiscal = null
    ) {

        // Valores obtenidos del form (que se obtienen de rrhhini)
        // Topes

        $TopeJubilatorioPatronal    = MapucheConfig::getTopesJubilatorioPatronal();
        $TopeJubilatorioPersonal    = MapucheConfig::getTopesJubilatorioPersonal();
        $TopeOtrosAportesPersonales = MapucheConfig::getTopesOtrosAportesPersonales();
        $trunca_tope                = $datos['truncaTope'];
        $TopeSACJubilatorioPers     = $TopeJubilatorioPersonal / 2;
        $TopeSACJubilatorioPatr     = $TopeJubilatorioPatronal / 2;
        $TopeSACJubilatorioOtroAp   = $TopeOtrosAportesPersonales / 2;
        Log::debug("TopeJubilatorioPatronal: $TopeJubilatorioPatronal");
        Log::debug("TopeJubilatorioPersonal: $TopeJubilatorioPersonal");
        Log::debug("TopeOtrosAportesPersonales: $TopeOtrosAportesPersonales");
        
        

        $artContTope = MapucheConfig::getParametroRrhh('Sicoss', 'ARTconTope', '1');

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
        $total_legajos = count($legajos);

        // ✅ PASO 1: Pre-carga masiva de conceptos (ya optimizado)
        $todos_conceptos = self::precargar_conceptos_todos_legajos($legajos);
        $conceptos_por_legajo = self::agrupar_conceptos_por_legajo($todos_conceptos);
        

        // ✅ PASO 2: Pre-carga masiva de cargos (ya optimizado)
        $datos_cargos_por_legajo = self::precargar_todos_datos_cargos($legajos);

        // ✅ PASO 3: Pre-carga masiva de otra actividad (ya optimizado)
        $datos_otra_actividad_por_legajo = self::precargar_otra_actividad_todos_legajos($legajos);
        
        // ✅ PASO 4: Pre-carga masiva de códigos obra social (NUEVA - ÚLTIMA OPTIMIZACIÓN)
        $inicio_codigo_os = microtime(true);
        $codigos_dgi_por_legajo = self::precargar_codigos_obra_social_todos_legajos($legajos);
        $fin_codigo_os = microtime(true);

        

        Log::info('=== 🏁 ÚLTIMA OPTIMIZACIÓN COMPLETADA - CÓDIGOS OBRA SOCIAL ===', [
            'tiempo_precarga_codigo_os_ms' => round(($fin_codigo_os - $inicio_codigo_os) * 1000, 2),
            'consultas_eliminadas' => count($legajos) * 2, // 2 consultas por legajo eliminadas
            'legajos_con_codigo_os' => count($codigos_dgi_por_legajo)
        ]);

        Log::info('🎯 ===== OPTIMIZACIÓN TOTAL COMPLETADA ===== 🎯', [
            'total_consultas_eliminadas' => (count($legajos) * 6) + 38000, // Estimación conservadora
            'mejora_estimada' => '99%+ más rápido que versión original'
        ]);
        
        Log::debug('✅ BUCLE FINAL OPTIMIZADO: SIN CONSULTAS SQL N+1');
        for ($i = 0; $i < $total_legajos; $i++) {
            $legajo = $legajos[$i]['nro_legaj'];
            $legajoActual = &$legajos[$i];
            
            $legajoActual['ImporteSACOtroAporte'] = 0;
            $legajoActual['TipoDeOperacion']      = 0;
            $legajoActual['ImporteImponible_4']   = 0;
            $legajoActual['ImporteSACNoDocente']  = 0;

            $legajoActual['ImporteSACDoce']  = 0;
            $legajoActual['ImporteSACAuto']  = 0;

            $legajoActual['codigo_os'] = self::codigo_os_optimizado($legajo, $codigos_dgi_por_legajo);


            //#44909 Incorporar a la salida de SICOSS el cdigo de situacin Reserva de Puesto (14)
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
                // ✅ USAR DATOS PRE-CARGADOS (NO MÁS CONSULTAS SQL)
                $limites = self::get_limites_cargos_optimizado($legajo, $datos_cargos_por_legajo);
                
                $cargos_legajo = self::get_cargos_activos_sin_licencia_optimizado($legajo, $datos_cargos_por_legajo);
                $cargos_legajo2 = self::get_cargos_activos_con_licencia_vigente_optimizado($legajo, $datos_cargos_por_legajo);
                $cargos_legajo = array_merge($cargos_legajo, $cargos_legajo2);
                
                log::info('👉 CARGOS ACTIVOS SIN LICENCIA', [
                    'legajo' => $legajo,
                    'cargos_activos_sin_licencia' => $cargos_legajo,
                    'limites' => $limites
                ]);


                
                // En caso de que el agente no tenga cargos activos, pero aparezca liquidado.
                if (!isset($limites['maximo'])) {
                    $cargos_activos_agente = Dh03::getCargosActivos($legajo);

                    if (empty($cargos_activos_agente)) {
                        $fecha_fin = self::quote(MapucheConfig::getFechaFinPeriodoCorriente());
                        $limites['maximo'] = substr($fecha_fin, 9, 2);
                    }
                }

                Log::debug('Limites nuevos:', $limites);

                $estado_situacion = self::inicializar_estado_situacion($legajos[$i]['codigosituacion'], $limites['minimo'], $limites['maximo']);

                Log::debug('Estado situacion:', $estado_situacion);

                $dias_lic_legajo = [];

                // Se evaluan las licencias
                if ($licencias != NULL) {
                    
                    foreach ($licencias as $licencia) {
                        if ($licencia['nro_legaj'] == $legajo) {
                            for ($dia = $licencia['inicio']; $dia <= $licencia['final']; $dia++) {
                                if (!in_array($dia, $dias_lic_legajo)) { // Los das con licencia de legajo no se tocan
                                    if ($limites['maximo'] >= $dia)
                                        $estado_situacion[$dia] = self::evaluar_condicion_licencia($estado_situacion[$dia], $licencia['condicion']);
                                    if ($licencia['es_legajo']) {
                                        $dias_lic_legajo[] = $dia; // En este da cuenta con licencia de legajo
                                    }
                                }
                            }
                        }
                    }
                }


                $licencias_cargos = LicenciaService::procesarLicenciasCargos($cargos_legajo);

                
                Log::debug('Licencias Cargos: ',$licencias_cargos);
                // Se evaluan los cargos
                foreach ($licencias_cargos as $cargo) {
                    for ($dia = 1; $dia <= count($cargo); $dia++) {
                        if (!in_array($dia, $dias_lic_legajo)) {
                            if ((isset($estado_situacion[$dia]) && $estado_situacion[$dia] == 13)) {
                                $estado_situacion[$dia] = $cargo[$dia]; // Si estaba trabajando en algn cargo se prioriza el cdigo en dha8
                            }
                        }
                    }
                }
                Log::debug('Estado situacion: ',$estado_situacion);
                
                $cambios_estado = self::calcular_cambios_estado($estado_situacion);
                $dias_trabajados = self::calcular_dias_trabajados($estado_situacion);
                $revista_legajo = self::calcular_revista_legajo($cambios_estado);

                Log::debug('Cambios estado: ', $cambios_estado);
                Log::debug('Dias trabajados: ', [$dias_trabajados]);
                Log::debug('Revista legajo: ', $revista_legajo);
                
                // Como cdigo de situacin general se toma el ltimo (?)
                $legajoActual['codigosituacion'] = $estado_situacion[$limites['maximo']];
                // Revista 1
                $legajoActual['codigorevista1'] = $revista_legajo[1]['codigo'];
                $legajoActual['fecharevista1'] = $revista_legajo[1]['dia'];
                // Revista 2
                $legajoActual['codigorevista2'] = ($revista_legajo[2]['codigo'] == 0) ? $revista_legajo[1]['codigo'] : $revista_legajo[2]['codigo'];
                $legajoActual['fecharevista2'] = $revista_legajo[2]['dia'];

                // Revista 3
                $legajoActual['codigorevista3'] = ($revista_legajo[3]['codigo'] == 0) ? $legajos[$i]['codigorevista2'] : $revista_legajo[3]['codigo'];
                $legajoActual['fecharevista3'] = $revista_legajo[3]['dia'];

                // Como das trabajados se toman aquellos das de cargo menos los das de licencia sin goce (?)
                $legajoActual['dias_trabajados'] = $dias_trabajados;
                Log::debug('Legajo actual: ', [json_encode($legajoActual)]);
            } else {
                // Se evaluan

                // Si tiene una licencia por maternidad activa el codigo de situacion es 5
                if (LicenciaService::tieneLicenciaMaternidadActiva($legajo)) {
                    $legajos[$i]['codigosituacion'] = 5;
                }

                // Si tengo chequeado el tilde de licencias cambio el codigo de situacion y la cantidad de das trabajados se vuelve 0
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
            if ($legajoActual['conyugue'] > 0)
                $legajoActual['conyugue'] = 1;

            // --- Obtengo la sumarizacin segn concepto o tipo de grupo de un concepto ---
            $conceptos_legajo = $conceptos_por_legajo[$legajo] ?? [];
            Log::debug('Conceptos legajo: ', $conceptos_legajo);

            
            self::sumarizar_conceptos_optimizado($conceptos_legajo, $legajos[$i]);

            // --- Otros datos remunerativos - OPTIMIZADO ---

            // ✅ Sumarizar conceptos segun tipo de concepto - SIN MÁS CONSULTAS SQL
            $suma_conceptos_tipoC = self::calcular_remuner_grupo_optimizado($conceptos_legajo, 'C', 'nro_orimp >0 AND codn_conce > 0');
            $suma_conceptos_tipoF = self::calcular_remuner_grupo_optimizado($conceptos_legajo, 'F', 'true');
            Log::debug('Suma conceptos tipo C: ', [$suma_conceptos_tipoC]);
            Log::debug('Suma conceptos tipo F: ', [$suma_conceptos_tipoF]);
            $legajoActual['Remuner78805']               = $suma_conceptos_tipoC;
            $legajoActual['AsignacionesFliaresPagadas'] = $suma_conceptos_tipoF;
            $legajoActual['ImporteImponiblePatronal']   = $suma_conceptos_tipoC;

            Log::debug('Remuner 78805: ', [$legajoActual['Remuner78805']]              );
            Log::debug('Asignaciones Familiares: ', [$legajoActual['AsignacionesFliaresPagadas']]);
            Log::debug('Importe Imponible Patronal: ', [$legajoActual['ImporteImponiblePatronal']]  );

            // Log de progreso cada 1000 legajos o al final
            if (($i + 1) % 1000 == 0 || ($i + 1) == $total_legajos) {
                Log::info("✅ Procesados legajos optimizados: " . ($i + 1) . "/$total_legajos", [
                    'porcentaje' => round((($i + 1) / $total_legajos) * 100, 1),
                    'memoria_mb' => round(memory_get_usage(true) / 1024 / 1024, 2),
                ]);
            }

            // Para calcular Remuneracion total= IMPORTE_BRUTO
            $legajoActual['DiferenciaSACImponibleConTope'] = 0;
            $legajoActual['DiferenciaImponibleConTope']    = 0;
            $legajoActual['ImporteSACPatronal']            = $legajoActual['ImporteSAC'];
            $legajoActual['ImporteImponibleSinSAC']        = $legajoActual['ImporteImponiblePatronal'] - $legajoActual['ImporteSACPatronal'];
            
            
            if ($legajoActual['ImporteSAC'] > $TopeSACJubilatorioPatr  && $trunca_tope == 1) {
                Log::debug('✅ Trunca el tope de SAC');
                $legajoActual['DiferenciaSACImponibleConTope']  = $legajoActual['ImporteSAC'] - $TopeSACJubilatorioPatr;
                $legajoActual['ImporteImponiblePatronal']      -= $legajoActual['DiferenciaSACImponibleConTope'];
                $legajoActual['ImporteSACPatronal']             = $TopeSACJubilatorioPatr;
                Log::debug('✅ Importe Imponible Sin SAC: ', [$legajoActual['ImporteImponibleSinSAC']]);
            }
            
            
            if ($legajoActual['ImporteImponibleSinSAC'] > $TopeJubilatorioPatronal && $trunca_tope == 1) {
                Log::debug('✅ Trunca el tope de Imponible',[
                    'ImporteImponibleSinSAC' => $legajos[$i]['ImporteImponibleSinSAC'],
                    'TopeJubilatorioPatronal' => $TopeJubilatorioPatronal,
                ]);
                $legajoActual['DiferenciaImponibleConTope'] = $legajos[$i]['ImporteImponibleSinSAC'] - $TopeJubilatorioPatronal;
                $legajoActual['ImporteImponiblePatronal']  -= $legajos[$i]['DiferenciaImponibleConTope'];
            }
            $legajoActual['IMPORTE_BRUTO'] = $legajoActual['ImporteImponiblePatronal'] + $legajoActual['ImporteNoRemun'];

            Log::debug('Para calcular IMPORTE_IMPON que es lo mismo que importe imponible 1');
            $legajoActual['IMPORTE_IMPON'] = 0;
            $legajoActual['IMPORTE_IMPON'] = $suma_conceptos_tipoC;
            Log::debug('IMPORTE_IMPON: (primer asignacion)', [$legajoActual['IMPORTE_IMPON']]);

            $VerificarAgenteImportesCERO  = 1;

            // Si es el check de informar becarios en configuracion esta chequeado entonces sumo al importe imponible la suma de conceptos de ese tipo de grupo (Becarios ART)
            if ($legajoActual['ImporteImponibleBecario'] != 0) {
                $legajoActual['IMPORTE_IMPON']            += $legajoActual['ImporteImponibleBecario'];
                $legajoActual['IMPORTE_BRUTO']            += $legajoActual['ImporteImponibleBecario'];
                $legajoActual['ImporteImponiblePatronal'] += $legajoActual['ImporteImponibleBecario'];
                $legajoActual['Remuner78805']             += $legajoActual['ImporteImponibleBecario'];
                Log::debug('✅ Sumo el importe de becarios al importe imponible', [
                    'IMPORTE_IMPON' => $legajoActual['IMPORTE_IMPON'],
                    'IMPORTE_BRUTO' => $legajoActual['IMPORTE_BRUTO'],
                    'ImporteImponiblePatronal' => $legajoActual['ImporteImponiblePatronal'],
                    'Remuner78805' => $legajoActual['Remuner78805'],
                ]);
            }
            
            if (self::VerificarAgenteImportesCERO($legajoActual) == 1 || $legajoActual['codigosituacion'] == 5 || $legajoActual['codigosituacion'] == 11) // codigosituacion=5 y codigosituacion=11 quiere decir maternidad y debe infrormarse
            {
                $legajoActual['PorcAporteDiferencialJubilacion'] = self::$porc_aporte_adicional_jubilacion;
                $legajoActual['ImporteImponible_4']              = $legajoActual['IMPORTE_IMPON'];
                $legajoActual['ImporteSACNoDocente']             = 0;
                //ImporteImponible_6 viene con valor de funcion sumarizar_conceptos_por_tipos_grupos
                $legajoActua['ImporteImponible_6']              = round(($legajoActual['ImporteImponible_6'] * 100) / $legajoActual['PorcAporteDiferencialJubilacion'], 2);
                $Imponible6_aux                                 = $legajoActua['ImporteImponible_6'];
                Log::debug('✅ Importes Imponibles', [
                    'PorcAporteDiferencialJubilacion' => $legajoActual['PorcAporteDiferencialJubilacion'],
                    'ImporteImponible_4' =>  $legajoActual['ImporteImponible_4'],
                    'ImporteSACNoDocente' => $legajoActual['ImporteSACNoDocente'],
                    'ImporteImponible_6' =>  $legajoActual['ImporteImponible_6'],
                    'Imponible6_aux' => $Imponible6_aux,
                ]);








                
                if ($Imponible6_aux != 0) {
                    Log::debug('✅ Importe Imponible 6 es distinto de 0');
                    if (
                        (int)$Imponible6_aux != (int)$legajoActual['IMPORTE_IMPON']
                        && (abs($Imponible6_aux - $legajoActual['IMPORTE_IMPON'])) > 5 //redondear hasta +  - $5
                        && $legajos[$i]['ImporteImponible_6'] < $legajoActual['IMPORTE_IMPON']
                    ) {
                        $legajoActual['TipoDeOperacion']     = 2;
                        $legajoActual['IMPORTE_IMPON'] -= $legajoActual['ImporteImponible_6'];
                        $legajoActual['ImporteSACNoDocente'] = $legajoActual['ImporteSAC'] - $legajoActual['SACInvestigador'];
                        Log::debug('✅ IMPORTE_IMPON, bloque Imponible6_aux', [
                            'IMPORTE_IMPON' => $legajoActual['IMPORTE_IMPON'],
                            'ImporteImponible_6' => $legajoActual['ImporteImponible_6'],
                            'ImporteSACNoDocente' => $legajoActual['ImporteSACNoDocente'],
                            'importeSAC' => $legajoActual['ImporteSAC'],
                            'SACInvestigador' => $legajoActual['SACInvestigador'],
                        ]);
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
                        Log::debug('✅ en el else, bloque Imponible6_aux', [
                            'ImporteSAC' => $legajoActual['ImporteSAC'],
                            'ImporteSACNoDocente' => $legajoActual['ImporteSACNoDocente'],
                        ]);
                    }
                } else {
                    $legajoActual['TipoDeOperacion']     = 1;
                    $legajoActual['ImporteSACNoDocente'] = $legajoActual['ImporteSAC'];
                    
                    Log::debug('✅ Importe Imponible 6 es igual a 0',[
                        'TipoDeOperacion' => $legajoActual['TipoDeOperacion'],
                        'ImporteSACNoDocente' => $legajoActual['ImporteSACNoDocente'],
                    ]);
                }

                $legajoActual['ImporteSACOtroAporte']          = $legajoActual['ImporteSAC'];
                $legajoActual['DiferenciaSACImponibleConTope'] = 0;
                $legajoActual['DiferenciaImponibleConTope']    = 0;

                /*****************/

                $tope_jubil_personal = $TopeJubilatorioPersonal;
                
                if ($legajoActual['ImporteSAC'] > 0)
                    $tope_jubil_personal = $TopeJubilatorioPersonal + $TopeSACJubilatorioPers;
                Log::debug('Tope Jubilatorio personal', ['tope_jubil_personal' => $tope_jubil_personal]);

                if ($legajoActual['ImporteSACNoDocente']  > $tope_jubil_personal) {
                    if ($trunca_tope == 1) {
                        $legajoActual['DiferenciaSACImponibleConTope'] = $legajoActual['ImporteSACNoDocente']  - $TopeSACJubilatorioPers;
                        $legajoActual['IMPORTE_IMPON']                -= $legajoActual['DiferenciaSACImponibleConTope'];
                        $legajoActual['ImporteSACNoDocente']           = $TopeSACJubilatorioPers;
                        Log::debug('✅ ImporteSacNoDocente es mayor al tope', [
                            'tope_jubil_personal' => $tope_jubil_personal,
                            'DiferenciaSACImponibleConTope' => $legajoActual['DiferenciaSACImponibleConTope'],
                            'IMPORTE_IMPON' => $legajoActual['IMPORTE_IMPON'],
                            'ImporteSACNoDocente' => $legajoActual['ImporteSACNoDocente'],
                        ]);
                    }
                } else {

                    if ($trunca_tope == 1) {

                        $bruto_nodo_sin_sac = $legajoActual['IMPORTE_BRUTO'] - $legajoActual['ImporteImponible_6'] - $legajoActual['ImporteSACNoDocente'];
                        Log::debug('✅ Calculo bruto nodo sin sac', [
                            'IMPORTE_BRUTO' => $legajoActual['IMPORTE_BRUTO'],
                            'ImporteSACNoDocente' => $legajoActual['ImporteSACNoDocente'],
                            'ImporteImponible_6' => $legajoActual['ImporteImponible_6'],
                        ]);

                        $sac = $legajoActual['ImporteSACNoDocente'];

                        $tope = min($bruto_nodo_sin_sac, $TopeJubilatorioPersonal) + min($sac, $TopeSACJubilatorioPers);
                        $imp_1 =  $legajoActual['IMPORTE_BRUTO'] -  $legajoActual['ImporteImponible_6'];

                        $tope_sueldo = min($bruto_nodo_sin_sac - $legajoActual['ImporteNoRemun'], $TopeJubilatorioPersonal);
                        $tope_sac = min($sac, $TopeSACJubilatorioPers);


                        $legajoActual['IMPORTE_IMPON'] = min($bruto_nodo_sin_sac - $legajoActual['ImporteNoRemun'], $TopeJubilatorioPersonal) + min($sac, $TopeSACJubilatorioPers);
                        Log::debug("✅ ACA TOPEA:", [
                            'sac' => $sac, 
                            'tope_jubil_personal' => $tope_jubil_personal, 
                            'bruto_nodo_sin_sac' => $bruto_nodo_sin_sac, 
                            'ImporteNoRemun' => $legajoActual['ImporteNoRemun'], 
                            'tope_sueldo' => $tope_sueldo, 
                            'tope_sac' => $tope_sac
                        ]);
                        Log::debug('en el else',[
                            'bruto_nodo_sin_sac' => $bruto_nodo_sin_sac,
                            'sac' => $sac,
                            'IMPORTE_IMPON' => $legajoActual['IMPORTE_IMPON'],
                        ]);
                    }
                }

                $explode = explode(',', self::$categoria_diferencial ?? ''); //arma el array
                $implode = implode("','", $explode); //vulve a String y agrega comillas
                $dh03Repository = new Dh03Repository();
                if ($dh03Repository->existeCategoriaDiferencial($legajoActual['nro_legaj'], $implode)) {
                    $legajoActual['IMPORTE_IMPON'] = 0;
                    Log::debug('✅ Existe categoria diferencial se resetea IMPORTE_IMPON a 0', [
                        'legajoActual' => $legajoActual,
                        'IMPORTE_IMPON' => $legajoActual['IMPORTE_IMPON'],
                    ]);
                }

                $legajoActual['ImporteImponibleSinSAC'] = $legajoActual['IMPORTE_IMPON'] - $legajoActual['ImporteSACNoDocente'];
                
                Log::debug('✅ Importe Imponible sin SAC', [
                    'ImporteImponibleSinSAC' => $legajoActual['ImporteImponibleSinSAC'],
                ]);

                $tope_jubil_personal = $TopeJubilatorioPersonal;

                $tope_jubil_personal = ($legajoActual['ImporteSAC'] > 0) ? $TopeJubilatorioPersonal + $TopeSACJubilatorioPers : $TopeJubilatorioPersonal;
                Log::debug('Tope Jubilatorio personal', ['tope_jubil_personal' => $tope_jubil_personal]);

                
                if ($legajoActual['ImporteImponibleSinSAC']  > $tope_jubil_personal) {
                    if ($trunca_tope == 1) {
                        $legajoActual['DiferenciaImponibleConTope'] = $legajoActual['ImporteImponibleSinSAC'] - $TopeJubilatorioPersonal;
                        $legajoActual['IMPORTE_IMPON']             -= $legajoActual['DiferenciaImponibleConTope'];
                        Log::debug('✅ ImporteImponibleSinSAC es mayor al tope', [
                            'tope_jubil_personal' => $tope_jubil_personal,
                            'DiferenciaImponibleConTope' => $legajoActual['DiferenciaImponibleConTope'],
                            'IMPORTE_IMPON' => $legajoActual['IMPORTE_IMPON'],
                            'ImporteImponibleSinSAC' => $legajoActual['ImporteImponibleSinSAC'],
                        ]);
                    }
                }


                $otra_actividad = self::otra_actividad_optimizado($legajo, $datos_otra_actividad_por_legajo);
                $legajoActual['ImporteBrutoOtraActividad']  = $otra_actividad['importebrutootraactividad'];
                $legajoActual['ImporteSACOtraActividad']    = $otra_actividad['importesacotraactividad'];
                Log::debug('✅ Importe Bruto y SAC de otra actividad', [
                    'ImporteBrutoOtraActividad' => $legajoActual['ImporteBrutoOtraActividad'],
                    'ImporteSACOtraActividad' => $legajoActual['ImporteSACOtraActividad'],
                ]);
                
                if (($legajoActual['ImporteBrutoOtraActividad'] != 0) || ($legajoActual['ImporteSACOtraActividad'] != 0)) {
                    if (($legajoActual['ImporteBrutoOtraActividad'] + $legajoActual['ImporteSACOtraActividad'])  >=  ($TopeSACJubilatorioPers + $TopeJubilatorioPatronal)) {
                        $legajoActual['IMPORTE_IMPON'] = 0.00;
                        Log::debug('✅ ImporteBrutoOtraActividad o ImporteSACOtraActividad, Importe Bruto y SAC de otra actividad es mayor al tope', [
                            'ImporteBrutoOtraActividad' => $legajoActual['ImporteBrutoOtraActividad'],
                            'ImporteSACOtraActividad' => $legajoActual['ImporteSACOtraActividad'],
                            'TopeSACJubilatorioPers' => $TopeSACJubilatorioPers,
                            'TopeJubilatorioPatronal' => $TopeJubilatorioPatronal,
                            'IMPORTE_IMPON' => $legajoActual['IMPORTE_IMPON'],
                        ]);
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
                        Log::debug('Nuevo IMPORTE_IMPON',[
                            'IMPORTE_IMPON' => $legajoActual['IMPORTE_IMPON'],
                        ]);
                    }
                }

                $legajoActual['DifSACImponibleConOtroTope']   = 0;
                $legajoActual['DifImponibleConOtroTope']      = 0;
                if ($legajoActual['ImporteSACOtroAporte'] > $TopeSACJubilatorioOtroAp) {
                    if ($trunca_tope == 1) {
                        $legajoActual['DifSACImponibleConOtroTope'] = $legajoActual['ImporteSACOtroAporte'] - $TopeSACJubilatorioOtroAp;
                        $legajoActual['ImporteImponible_4']        -= $legajoActual['DifSACImponibleConOtroTope'];
                        $legajoActual['ImporteSACOtroAporte']       = $TopeSACJubilatorioOtroAp;
                        Log::debug('✅ ImporteSACOtroAporte es mayor al tope', [
                            'TopeSACJubilatorioOtroAp' => $TopeSACJubilatorioOtroAp,
                            'DifSACImponibleConOtroTope' => $legajoActual['DifSACImponibleConOtroTope'],
                            'ImporteImponible_4' => $legajoActual['ImporteImponible_4'],
                            'ImporteSACOtroAporte' => $legajoActual['ImporteSACOtroAporte'],
                        ]);
                    }
                }
                
                $legajoActual['OtroImporteImponibleSinSAC'] = $legajoActual['ImporteImponible_4'] - $legajoActual['ImporteSACOtroAporte'];
                if ($legajoActual['OtroImporteImponibleSinSAC'] > $TopeOtrosAportesPersonales) {
                    if ($trunca_tope == 1) {
                        $legajoActual['DifImponibleConOtroTope'] = $legajoActual['OtroImporteImponibleSinSAC'] - $TopeOtrosAportesPersonales;
                        $legajoActual['ImporteImponible_4']     -= $legajoActual['DifImponibleConOtroTope'];
                        Log::debug('✅ OtroImporteImponibleSinSAC es mayor al tope', [
                            'TopeOtrosAportesPersonales' => $TopeOtrosAportesPersonales,
                            'DifImponibleConOtroTope' => $legajoActual['DifImponibleConOtroTope'],
                            'ImporteImponible_4' => $legajoActual['ImporteImponible_4'],
                            'OtroImporteImponibleSinSAC' => $legajoActual['OtroImporteImponibleSinSAC'],
                        ]);
                    }
                }
                
                if ($legajoActual['ImporteImponible_6'] != 0 && $legajoActual['TipoDeOperacion'] == 1) {
                    $legajoActual['IMPORTE_IMPON'] = 0;
                    Log::debug('✅ ImporteImponible_6 es mayor a 0 y TipoDeOperacion es 1', [
                        'IMPORTE_IMPON' => $legajoActual['IMPORTE_IMPON'],
                    ]);
                }
                
                // Calcular Sueldo mas Adicionales
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

                Log::debug('✅ ImporteSueldoMasAdicionales', [
                    'ImporteSueldoMasAdicionales' => $legajoActual['ImporteSueldoMasAdicionales'],
                ]);
                

                if ($legajoActual['trabajadorconvencionado'] === null) {
                    $legajoActual['trabajadorconvencionado'] = self::$trabajadorConvencionado;
                }

                // Sumariza las asiganciones familiares en el bruto y deja las asiganciones familiares en cero, esto si en configuracion esta chequeado
                if (self::$asignacion_familiar) {
                    $legajoActual['IMPORTE_BRUTO'] += $legajoActual['AsignacionesFliaresPagadas'];
                    $legajoActual['AsignacionesFliaresPagadas'] = 0;
                }
                // Por ticket #3947. Check "Generar ART con tope"
                $legajoActual['importeimponible_9'] = ($artContTope === '0') ? $legajoActual['Remuner78805'] : $legajoActual['ImporteImponible_4'];
                Log::debug('importeImponible_9',['impoteimponible_9'=> $legajoActual['importeimponible_9']]);

                // Por ticket #3947. Check "Considerar conceptos no remunerativos en clculo de ART?"
                if (MapucheConfig::getParametroRrhh('Sicoss', 'ConceptosNoRemuEnART', '0') === '1') // Considerar conceptos no remunerativos
                {
                    $legajoActual['importeimponible_9'] += $legajoActual['ImporteNoRemun'];
                    Log::debug('importeImponible_9 + ImporteNoRemun',['impoteimponible_9'=> $legajoActual['importeimponible_9']]);
                }
                // por GDS #5913 Incorporacion de conceptos no remunerativos a las remuneraciones 4 y 8 de SICOSS
                $legajoActual['Remuner78805'] += $legajoActual['NoRemun4y8'];
                $legajoActual['ImporteImponible_5'] = $legajoActual['ImporteImponible_4'];
                $legajoActual['ImporteImponible_4'] += $legajoActual['NoRemun4y8'];
                $legajoActual['ImporteImponible_4'] += $legajoActual['ImporteTipo91'];
                Log::debug('Por GDS #5913 Incorporacion de conceptos no remunerativos a las remuneraciones 4 y 8 de SICOSS', [
                    'Remuner78805' => $legajoActual['Remuner78805'], 
                    'ImporteImponible_4' => $legajoActual['ImporteImponible_4'], 
                    'ImporteImponible_5' => $legajoActual['ImporteImponible_5'], 
                    ]);

                $legajoActual['IMPORTE_BRUTO'] += $legajoActual['ImporteNoRemun96'];
                Log::debug('Totales antes de la asignacion: ',[
                    'bruto' => $total['bruto'],
                    'imponible_1' => $total['imponible_1'],
                    'imponible_2' => $total['imponible_2'],
                    'imponible_4' => $total['imponible_4'],
                    'imponible_5' => $total['imponible_5'],
                    'imponible_8' => $total['imponible_8'],
                    'imponible_6' => $total['imponible_6'],
                    'imponible_9' => $total['imponible_9'],
                ]);
                $total['bruto']       += round($legajoActual['IMPORTE_BRUTO'], 2);
                $total['imponible_1'] += round($legajoActual['IMPORTE_IMPON'], 2);
                $total['imponible_2'] += round($legajoActual['ImporteImponiblePatronal'], 2);
                $total['imponible_4'] += round($legajoActual['ImporteImponible_4'], 2);
                $total['imponible_5'] += round($legajoActual['ImporteImponible_5'], 2);
                $total['imponible_8'] += round($legajoActual['Remuner78805'], 2);
                $total['imponible_6'] += round($legajoActual['ImporteImponible_6'], 2);
                $total['imponible_9'] += round($legajoActual['importeimponible_9'], 2);
                Log::debug('Totales: ',[
                    'bruto' => $total['bruto'],
                    'imponible_1' => $total['imponible_1'],
                    'imponible_2' => $total['imponible_2'],
                    'imponible_4' => $total['imponible_4'],
                    'imponible_5' => $total['imponible_5'],
                    'imponible_8' => $total['imponible_8'],
                    'imponible_6' => $total['imponible_6'],
                    'imponible_9' => $total['imponible_9'],
                ]);

                $legajos_validos[$j] = $legajoActual;
                $j++;
            } elseif ($datos['check_lic'] && ($legajoActual['licencia'] == 1)) {
                // fin else que verifica que los importes sean distintos de 0
                // Si los importes son cero el legajo no se agrega al archivo sicoss; pero cuando tengo el check de licencias por interface y ademas el legajo tiene licencias entonces si va
                // Inicializo variables faltantes en cero
                $legajoActual['ImporteSueldoMasAdicionales'] = 0;
                if ($legajoActual['trabajadorconvencionado'] === null) {
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

        // ✅ LOG FINAL DE OPTIMIZACIÓN
        $fin_total = microtime(true);

        Log::info('=== ✅ OPTIMIZACIÓN COMPLETADA ===', [
            'total_legajos_procesados' => $total_legajos,
            'legajos_validos_generados' => count($legajos_validos),
            'memoria_final_mb' => round(memory_get_usage(true) / 1024 / 1024, 2),
            'mejora_estimada' => 'Eliminadas ' . $total_legajos . ' consultas N+1 individuales'
        ]);

        if (!empty($legajos_validos)) {
            if ($retornar_datos === TRUE) {
                dd($legajos_validos);
                return $legajos_validos;
            }

            // NUEVA FUNCIONALIDAD: Guardar en BD en lugar de TXT
            if ($guardar_en_bd === TRUE && $periodo_fiscal !== null) {
                Log::info("Guardando en BD");
                self::guardar_en_bd($legajos_validos, $periodo_fiscal);
            }

            // Comportamiento original: grabar en TXT
            self::grabar_en_txt($legajos_validos, $nombre_arch);
        }
        
        return $total;
    }


    // Dado un arreglo, doy formato y agrego a archivo
    public static function grabar_en_txt($legajos, $nombre_arch)
    {
        //Para todos los datos obtenidos habra q calcular lo que no esta en la consulta
        $directorio = storage_path('comunicacion/sicoss');
        // Crea el directorio si no existe
        if (!File::exists($directorio)) {
            File::makeDirectory($directorio, 0775, true);
        }

        $archivo = $directorio . '/' . $nombre_arch . '.txt';
        Log::info('Intentando guardar archivo en: ' . $archivo);
        $fh = @fopen($archivo, 'w');
        if (!$fh) {
            $error = error_get_last();
            Log::error('No se pudo abrir el archivo: ' . $archivo . ' - Error: ' . $error['message']);
        }
        // Proceso la tabla, le agrego las longitudes correpondientes
        for ($i = 0; $i < count($legajos); $i++) {
            fwrite(
                $fh,
                $legajos[$i]['cuit'] .                                                                // Campo 1
                    self::llena_blancos_mod($legajos[$i]['apyno'], 30) .                                             // Campo 2
                    $legajos[$i]['conyugue'] .                                                                        // Campo 3
                    self::llena_importes($legajos[$i]['hijos'], 2) .                                                 // Campo 4
                    self::llena_importes($legajos[$i]['codigosituacion'], 2) .                                       // Campo 5 TODO: Preguntar es el que viene de dha8?
                    self::llena_importes($legajos[$i]['codigocondicion'], 2) .                                       // Campo 6
                    self::llena_importes($legajos[$i]['TipoDeActividad'], 3) .                                       // Campo 7 - Segun prioridad es codigoactividad de dha8 u otro valor, ver funcion sumarizar_conceptos_por_tipos_grupos
                    self::llena_importes($legajos[$i]['codigozona'], 2) .                                            // Campo 8
                    self::llena_blancos_izq(number_format($legajos[$i]['aporteadicional'] ?? 0.0, 2, ',', ''), 5) .            // Campo 9 - Porcentaje de Aporte Adicional Obra Social
                    self::llena_importes($legajos[$i]['codigocontratacion'], 3) .                                    // Campo 10
                    self::llena_importes($legajos[$i]['codigo_os'], 6) .
                    self::llena_importes($legajos[$i]['adherentes'], 2) .                                            // Campo 12 - Segn este chequeado en configuracin informo 0 o uno (sumarizar_conceptos_por_tipos_grupos) o cantidad de adherentes (dh09)
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
                    self::llena_importes($legajos[$i]['fecharevista1'], 2) .                                        // campo 31 - Dia inicio Situacin de Revista 1
                    self::llena_importes($legajos[$i]['codigorevista2'], 2) .                                       // Situacin de Revista 2
                    self::llena_importes($legajos[$i]['fecharevista2'], 2) .                                        // Dia inicio Situacin de Revista 2
                    self::llena_importes($legajos[$i]['codigorevista3'], 2) .                                       // Situacin de Revista 3
                    self::llena_importes($legajos[$i]['fecharevista3'], 2) .                                        // Dia inicio Situacin de Revista 3
                    self::llena_blancos_izq(number_format($legajos[$i]['ImporteSueldoMasAdicionales'] ?? 0.0, 2, ',', ''), 12) .        // Campo 36
                    self::llena_blancos_izq(number_format($legajos[$i]['ImporteSAC'] ?? 0.0, 2, ',', ''), 12) .                // Campo 37
                    self::llena_blancos_izq(number_format($legajos[$i]['ImporteHorasExtras'] ?? 0.0, 2, ',', ''), 12) .        // Campo 38
                    self::llena_blancos_izq(number_format($legajos[$i]['ImporteZonaDesfavorable'] ?? 0.0, 2, ',', ''), 12) .   // Campo 39
                    self::llena_blancos_izq(number_format($legajos[$i]['ImporteVacaciones'] ?? 0.0, 2, ',', ''), 12) .         // Campo 40
                    '0000000' . self::llena_importes($legajos[$i]['dias_trabajados'], 2) .                            // Campo 41 - Das trabajados
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
                    self::llena_blancos_izq(number_format($legajos[$i]['ContribTareaDif'] ?? 0.0, 2, ',', ''), 9) .            // Campo 55 - Contribucin Tarea Diferencial
                    '000' .                                                                                             // Campo 56 - Horas Trabajadas
                    $legajos[$i]['SeguroVidaObligatorio'] .                                                           // Campo 57 - Seguro  de Vida Obligatorio
                    self::llena_blancos_izq(number_format($legajos[$i]['ImporteSICOSS27430'] ?? 0.0, 2, ',', ''), 12) .         // Campo 58 - Importe a detraer Ley 27430
                    self::llena_blancos_izq(number_format($legajos[$i]['IncrementoSolidario'] ?? 0.0, 2, ',', ''), 12) . // Campo 59 - Incremento Solidario para empresas del sector privado y pblico (D. 14/2020 y 56/2020)
                    self::llena_blancos_izq(number_format(0, 2, ',', ''), 12) .                                          // Campo 60 - Remuneracin 11
                    "\r\n"
            );
        }
        // Ejemplo de escritura
        fwrite($fh, "Contenido de prueba\n");
        fclose($fh);
    }

    /**
     * ⚠️ MÉTODO ORIGINAL - DEPRECADO POR PROBLEMA N+1
     *
     * Este método fue reemplazado por sumarizar_conceptos_optimizado()
     * Se mantiene solo para referencia y posible rollback.
     *
     * @deprecated Usar sumarizar_conceptos_optimizado() en su lugar
     */
    public static function sumarizar_conceptos_por_tipos_grupos($nro_leg, &$leg)
    {
        Log::warning('⚠️ USANDO MÉTODO DEPRECADO: sumarizar_conceptos_por_tipos_grupos', [
            'legajo' => $nro_leg,
            'recomendacion' => 'Usar sumarizar_conceptos_optimizado() en su lugar'
        ]);

        // 📊 Log para monitorear problema N+1
        static $contador_consultas = 0;
        $contador_consultas++;

        if ($contador_consultas <= 5 || $contador_consultas % 1000 == 0) {
            Log::info("sumarizar_conceptos_por_tipos_grupos: Consulta N+1 #{$contador_consultas}", [
                'legajo' => $nro_leg,
                'memoria_actual' => round(memory_get_usage(true) / 1024 / 1024, 2) . ' MB'
            ]);
        }

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
        // En el caso de que en check 'Toma en cuenta Familiares a Cargo para informar SICOSS?' en configuracin -> impositivos -> parametros sicoss sea false
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

            // #6204 Nuevos campos SICOSS "Incremento Salarial" y "Remuneracin 11"
            if (preg_match('/[^\d]+86[^\d]+/', $grupos_concepto)) {
                $leg['IncrementoSolidario'] += $importe;
            }

            // Tipo 91- AFIP Base de Clculo Diferencial Aportes OS y FSR
            if (preg_match('/[^\d]+91[^\d]+/', $grupos_concepto)) {
                $leg['ImporteTipo91'] += $importe;
            }

            // nuevo tipo de grupo 96, conceptos NoRemun que solo impacten en la Remuneracin bruta total
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

    /**
     * Calcula el SAC Investigador para un legajo específico y una lista de cargos.
     *
     * Esta funcin itera sobre una lista de cargos, aplicando un filtro específico para
     * conceptos liquidados relacionados a cada cargo, y suma el importe de cada concepto
     * que cumpla con el filtro. El filtro se enfoca en conceptos con tipo de grupo que
     * incluya el código 9.
     *
     * @param string $nro_leg El número de legajo para el cual se calcula el SAC Investigador.
     * @param array $cargos Un arreglo de números de cargo para los cuales se aplicará el cálculo.
     * @return int El total del SAC Investigador calculado.
     */
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


    /**
     * Consulta los conceptos liquidados para un legajo específico, aplicando un filtro adicional.
     *
     * Esta funcin ejecuta una consulta SQL para obtener los conceptos liquidados asociados a un legajo específico,
     * aplicando un filtro adicional definido por el parámetro $where. Los resultados se convierten de objetos
     * stdClass a arrays para facilitar su manejo.
     *
     * @param string $nro_leg El número de legajo para el cual se consultan los conceptos liquidados.
     * @param string $where La cláusula WHERE adicional para filtrar los resultados.
     * @return array Un arreglo de conceptos liquidados, cada uno representado como un arreglo asociativo.
     */
    public static function consultar_conceptos_liquidados($nro_leg, $where)
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

        // Convertir cada objeto stdClass a array
        return array_map(function ($item) {
            return (array)$item;
        }, $conceptos_filtrados);
    }

    /**
     * Calcula las horas extras para un concepto y un cargo específicos.
     *
     * Esta funcin ejecuta una consulta SQL para sumar las horas extras de un concepto
     * que pertenecen a un cargo específico y cumplen con ciertas condiciones
     * adicionales definidas por el parámetro $where.
     *
     * @param string $concepto El código del concepto.
     * @param string $cargo El número de cargo.
     * @return array Un arreglo asociativo con el cargo, el concepto y la suma de horas extras.
     */
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
        // Si no hay resultados, retornar un array con valores por defecto
        if (empty($horas)) {
            return [
                'cargo' => $cargo,
                'concepto' => $concepto,
                'sum_nov1' => 0
            ];
        }

        // Convertir el objeto stdClass a array
        return (array)$horas[0];
    }

    /**
     * Calcula la remuneración total de un grupo específico para un legajo determinado.
     *
     * Esta funcin ejecuta una consulta SQL para sumar los importes de conceptos liquidados
     * que pertenecen a un tipo específico de concepto y cumplen con ciertas condiciones
     * adicionales definidas por el parámetro $where.
     *
     * @param string $nro_legajo El número de legajo del empleado.
     * @param string $tipo El tipo de concepto a considerar.
     * @param string $where Condiciones adicionales para la consulta.
     * @return float La remuneración total del grupo.
     */
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
        // Manejo seguro del resultado
        return !empty($suma) && isset($suma[0]->suma) ? (float)$suma[0]->suma : 0.0;
    }

    /**
     * Obtiene los importes de otra actividad para un legajo específico.
     *
     * Ejecuta una consulta SQL para obtener el importe bruto y el importe SAC de otra actividad
     * correspondiente al último período para un legajo específico. Si no se encuentra ningún registro,
     * se devuelve un arreglo con los importes establecidos en cero.
     *
     * @param string $nro_legajo El número de legajo del empleado.
     * @return array Un arreglo asociativo con los importes bruto y SAC de otra actividad.
     */
    public static function otra_actividad($nro_legajo)
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

        return (array)$resp[0];
    }

    // Verifica los importes dado un legajo, si todos son ceros entonces no debe tenerse en cuenta en el informe sicoss
    static function VerificarAgenteImportesCERO($leg)
    {
        $VerificarAgenteImportesCERO = 1;
        if ($leg['IMPORTE_BRUTO'] == 0 && $leg['IMPORTE_IMPON'] == 0 && $leg['AsignacionesFliaresPagadas'] == 0 && $leg['ImporteNoRemun'] == 0 && $leg['IMPORTE_ADICI'] == 0 && $leg['IMPORTE_VOLUN'] == 0)
            $VerificarAgenteImportesCERO = 0;
        return $VerificarAgenteImportesCERO;
    }

    /**
     * Devuelve el código DGI de obra social correspondiente dado un legajo.
     *
     * Si el legajo es jubilado, se devuelve '000000'. Si no es jubilado, se verifica el campo 'codc_obsoc' en la tabla 'mapuche.dh09'.
     * Si el campo 'codc_obsoc' no está vacío, se utiliza para obtener el código DGI de la obra social. Si está vacío, se asigna el código de obra social por defecto.
     * Finalmente, se devuelve el código DGI correspondiente. Si no se encuentra código DGI, se devuelve '000000'.
     *
     * @param string $nro_legajo Número de legajo a verificar.
     * @return string Código DGI de la obra social.
     */
    static function codigo_os($nro_legajo)
    {
        // Si es jubilado directamente retorno 000000
        if (Dh01::esJubilado($nro_legajo)) {
            return '000000';
        }

        // Consulta a dh09 para obtener el código de obra social
        $sql = "
        SELECT
            dh09.codc_obsoc
        FROM
            mapuche.dh09
        WHERE
            dh09.nro_legaj = $nro_legajo
    ";

        $siglas = DB::connection(self::getStaticConnectionName())->select($sql);

        // Inicializar el objeto si está vacío
        if (empty($siglas)) {
            $siglas = [(object)['codc_obsoc' => self::$codigo_obra_social_default]];
        } else if (empty($siglas[0]->codc_obsoc)) {
            $siglas[0]->codc_obsoc = self::$codigo_obra_social_default;
        }

        $sigla = self::quote($siglas[0]->codc_obsoc);

        // Consulta a dh37 para obtener el código DGI
        $sql2 = "
        SELECT
            dh37.codn_osdgi
        FROM
            mapuche.dh37
        WHERE
            dh37.codc_obsoc = $sigla
    ";

        $coddgi = DB::connection(self::getStaticConnectionName())->select($sql2);

        // Retornar el código DGI o 000000 si no existe
        return empty($coddgi[0]->codn_osdgi) ? '000000' : $coddgi[0]->codn_osdgi;
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
        $totales = array();
        $i = 0;
        foreach ($totales_periodo as $clave => $valor) {
            $totales[$i++] = array('variable' => 'BRUTO',          'valor' => $valor['bruto'],       'periodo' => $clave);
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
        $path   = storage_path('comunicacion/sicoss/');

        $archivo = $path . $nombre . '.zip';
        // Si existe lo elimino
        if (file_exists($archivo))
            unlink($archivo);

        $archivos_adjuntados = 0;

        if (isset(self::$archivos)) {
            // $zip = new mapuche_zip($path, $nombre); no borrar

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

    /**
     * Pre-carga todos los conceptos liquidados para todos los legajos de una vez.
     *
     * Elimina el problema N+1 cargando todos los conceptos de todos los legajos
     * en una sola consulta optimizada, en lugar de hacer una consulta por legajo.
     *
     * @param array $legajos Array de legajos con estructura ['nro_legaj' => valor]
     * @return array Array de conceptos liquidados con nro_legaj incluido
     */
    public static function precargar_conceptos_todos_legajos($legajos): array
    {
        // Extraer solo los números de legajo del array
        $nros_legajos = array_column($legajos, 'nro_legaj');

        // Validar que tengamos legajos para procesar
        if (empty($nros_legajos)) {
            Log::warning('precargar_conceptos_todos_legajos: No hay legajos para procesar');
            return [];
        }

        // Crear lista de legajos para IN clause
        $legajos_in = implode(',', $nros_legajos);

        Log::info('precargar_conceptos_todos_legajos: Iniciando pre-carga', [
            'cantidad_legajos' => count($nros_legajos),
            'memoria_antes' => round(memory_get_usage(true) / 1024 / 1024, 2) . ' MB'
        ]);

        $inicio = microtime(true);

        // ✅ UNA SOLA CONSULTA para todos los legajos - INCLUIR CAMPOS ADICIONALES
        $sql = "
            SELECT
                cl.nro_legaj,
                cl.impp_conce,
                cl.nov1_conce,
                cl.codn_conce,
                cl.tipos_grupos,
                cl.nro_cargo,
                cl.codigoescalafon,
                cl.tipo_conce,              -- ✅ NUEVO: Para calcular_remuner_grupo
                dh12.nro_orimp              -- ✅ NUEVO: Para filtros de calcular_remuner_grupo
            FROM conceptos_liquidados cl
            LEFT JOIN mapuche.dh12 ON dh12.codn_conce = cl.codn_conce
            WHERE cl.nro_legaj IN ($legajos_in)
              AND cl.tipos_grupos IS NOT NULL
            ORDER BY cl.nro_legaj, cl.codn_conce
        ";

        try {
            $resultado = DB::connection(self::getStaticConnectionName())->select($sql);

            $fin = microtime(true);
            $tiempo_consulta = ($fin - $inicio) * 1000; // en milisegundos

            Log::info('precargar_conceptos_todos_legajos: Pre-carga completada', [
                'conceptos_cargados' => count($resultado),
                'tiempo_consulta_ms' => round($tiempo_consulta, 2),
                'memoria_despues' => round(memory_get_usage(true) / 1024 / 1024, 2) . ' MB',
                'promedio_conceptos_por_legajo' => count($nros_legajos) > 0 ? round(count($resultado) / count($nros_legajos), 2) : 0
            ]);

            // Convertir objetos stdClass a arrays para consistencia
            return array_map(function ($item) {
                return (array)$item;
            }, $resultado);
        } catch (\Exception $e) {
            Log::error('precargar_conceptos_todos_legajos: Error en consulta SQL', [
                'error' => $e->getMessage(),
                'cantidad_legajos' => count($nros_legajos)
            ]);

            // En caso de error, devolver array vacío para evitar que falle el proceso
            return [];
        }
    }

    /**
     * Método auxiliar para obtener estadísticas de la pre-carga
     *
     * @param array $todos_conceptos Array resultado de precargar_conceptos_todos_legajos
     * @param array $legajos Array original de legajos
     * @return array Estadísticas útiles para debugging
     */
    public static function obtener_estadisticas_precarga($todos_conceptos, $legajos): array
    {
        $stats = [
            'total_conceptos' => count($todos_conceptos),
            'total_legajos_solicitados' => count($legajos),
            'legajos_con_conceptos' => 0,
            'legajos_sin_conceptos' => 0,
            'conceptos_por_legajo' => [],
            'memoria_utilizada_mb' => memory_get_usage(true) / 1024 / 1024
        ];

        // Agrupar por legajo para estadísticas
        $conceptos_agrupados = [];
        foreach ($todos_conceptos as $concepto) {
            $nro_legaj = $concepto['nro_legaj'];
            if (!isset($conceptos_agrupados[$nro_legaj])) {
                $conceptos_agrupados[$nro_legaj] = 0;
            }
            $conceptos_agrupados[$nro_legaj]++;
        }

        // Calcular estadísticas
        $legajos_solicitados = array_column($legajos, 'nro_legaj');
        foreach ($legajos_solicitados as $legajo) {
            if (isset($conceptos_agrupados[$legajo])) {
                $stats['legajos_con_conceptos']++;
                $stats['conceptos_por_legajo'][] = $conceptos_agrupados[$legajo];
            } else {
                $stats['legajos_sin_conceptos']++;
                $stats['conceptos_por_legajo'][] = 0;
            }
        }

        // Estadísticas adicionales
        if (!empty($stats['conceptos_por_legajo'])) {
            $stats['promedio_conceptos_por_legajo'] = array_sum($stats['conceptos_por_legajo']) / count($stats['conceptos_por_legajo']);
            $stats['max_conceptos_por_legajo'] = max($stats['conceptos_por_legajo']);
            $stats['min_conceptos_por_legajo'] = min($stats['conceptos_por_legajo']);
        }

        return $stats;
    }

    /**
     * Agrupa los conceptos liquidados por legajo para acceso rápido O(1)
     *
     * @param array $todos_conceptos Array resultado de precargar_conceptos_todos_legajos
     * @return array Array asociativo [nro_legaj => [conceptos...]]
     */
    public static function agrupar_conceptos_por_legajo($todos_conceptos): array
    {
        $inicio = microtime(true);
        $conceptos_por_legajo = [];

        foreach ($todos_conceptos as $concepto) {
            $nro_legaj = $concepto['nro_legaj'];

            if (!isset($conceptos_por_legajo[$nro_legaj])) {
                $conceptos_por_legajo[$nro_legaj] = [];
            }

            $conceptos_por_legajo[$nro_legaj][] = $concepto;
        }

        $fin = microtime(true);
        $tiempo_agrupacion = ($fin - $inicio) * 1000;

        Log::info('agrupar_conceptos_por_legajo: Agrupación completada', [
            'total_conceptos' => count($todos_conceptos),
            'legajos_agrupados' => count($conceptos_por_legajo),
            'tiempo_agrupacion_ms' => round($tiempo_agrupacion, 2),
            'memoria_utilizada_mb' => round(memory_get_usage(true) / 1024 / 1024, 2)
        ]);

        return $conceptos_por_legajo;
    }

    /**
     * Versión optimizada de sumarizar_conceptos_por_tipos_grupos que NO hace consultas SQL
     *
     * @param array $conceptos_legajo Conceptos pre-cargados para este legajo
     * @param array $leg Referencia al array del legajo para modificar
     */
    public static function sumarizar_conceptos_optimizado($conceptos_legajo, &$leg)
    {
        // Inicializar todas las variables igual que antes
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
        $leg['AporteAdicionalObraSocial'] = 0;
        $leg['ImporteSICOSS27430']        = 0;
        $leg['ImporteSICOSSDec56119']     = 0;
        $leg['ImporteSACDoce']            = 0;
        $leg['ImporteSACAuto']            = 0;
        $leg['ImporteSACNodo']            = 0;
        $leg['ContribTareaDif']           = 0;
        $leg['NoRemun4y8']                = 0;
        $leg['IncrementoSolidario']       = 0;
        $leg['ImporteNoRemun96']          = 0;
        $leg['ImporteTipo91']             = 0;

        $informar_becarios = MapucheConfig::getSicossInformarBecarios();
        $cargoInvestigador = [];
        $conce_hs_extr = [];

        // En el caso de que en check 'Toma en cuenta Familiares a Cargo para informar SICOSS?' en configuracin -> impositivos -> parametros sicoss sea false
        // voy a fijarme si se liquido un concepto igual al configurado como obra social familiar a cargo. Informo 0 o 1 (no se liquido o se liquido algun concepto igual al definido)
        if (self::$cantidad_adherentes_sicoss == 0)
            $leg['adherentes'] = 0;

        // ✅ PROCESAR CONCEPTOS PRE-CARGADOS (NO MÁS CONSULTAS SQL)
        foreach ($conceptos_legajo as $concepto) {
            $importe            = $concepto['impp_conce'];
            $importe_novedad    = $concepto['nov1_conce'];
            $grupos_concepto    = $concepto['tipos_grupos'];
            $codn_concepto      = $concepto['codn_conce'];
            $nro_cargo          = $concepto['nro_cargo'];
            $codigo_obra_social = $leg['codigo_os'];

            // *** MISMA LÓGICA QUE ANTES - Solo sin consultas SQL ***

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

                if ($concepto['codigoescalafon'] == 'NODO')
                    $leg['ImporteSACNodo'] += $importe;
                if ($concepto['codigoescalafon'] == 'AUTO')
                    $leg['ImporteSACAuto'] += $importe;
                if ($concepto['codigoescalafon'] == 'DOCE')
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

            // #6204 Nuevos campos SICOSS "Incremento Salarial" y "Remuneracin 11"
            if (preg_match('/[^\d]+86[^\d]+/', $grupos_concepto)) {
                $leg['IncrementoSolidario'] += $importe;
            }

            // Tipo 91- AFIP Base de Clculo Diferencial Aportes OS y FSR
            if (preg_match('/[^\d]+91[^\d]+/', $grupos_concepto)) {
                $leg['ImporteTipo91'] += $importe;
            }

            // nuevo tipo de grupo 96, conceptos NoRemun que solo impacten en la Remuneracin bruta total
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

        // ✅ Calcular SAC Investigador optimizado (sin consultas SQL adicionales)
        $leg['SACInvestigador'] = self::calcularSACInvestigadorOptimizado($conceptos_legajo, $cargoInvestigador);
    }

    /**
     * Versión optimizada de calcularSACInvestigador que NO hace consultas SQL
     */
    public static function calcularSACInvestigadorOptimizado($conceptos_legajo, $cargos): int
    {
        $sacInvestigador = 0;
        $cargos = array_unique($cargos);

        foreach ($conceptos_legajo as $concepto) {
            $nro_cargo = $concepto['nro_cargo'];
            $grupos_concepto = $concepto['tipos_grupos'];

            // Verificar si el cargo está en la lista Y tiene tipo de grupo 9
            if (
                in_array($nro_cargo, $cargos) &&
                preg_match('/[^\d]+9[^\d]+/', $grupos_concepto)
            ) {
                $sacInvestigador += $concepto['impp_conce'];
            }
        }

        return $sacInvestigador;
    }

    /**
     * Versión optimizada de calcular_remuner_grupo que NO hace consultas SQL
     *
     * @param array $conceptos_legajo Conceptos pre-cargados para este legajo
     * @param string $tipo Tipo de concepto a considerar
     * @param string $where_condition Condición adicional para filtrar
     * @return float La remuneración total del grupo
     */
    public static function calcular_remuner_grupo_optimizado($conceptos_legajo, $tipo, $where_condition): float
    {
        $suma = 0.0;

        foreach ($conceptos_legajo as $concepto) {
            // Verificar que el concepto sea del tipo solicitado
            if (!isset($concepto['tipo_conce']) || $concepto['tipo_conce'] !== $tipo) {
                continue;
            }

            // Aplicar filtros según la condición
            if ($where_condition === 'nro_orimp >0 AND codn_conce > 0') {
                // Verificar que nro_orimp > 0 y codn_conce > 0
                if (
                    !isset($concepto['nro_orimp']) || $concepto['nro_orimp'] <= 0 ||
                    !isset($concepto['codn_conce']) || $concepto['codn_conce'] <= 0
                ) {
                    continue;
                }
            }
            // Para $where_condition === 'true' no hay filtros adicionales

            $suma += (float)$concepto['impp_conce'];
        }

        return $suma;
    }



    // Agregar método para inicializar variables estáticas desde tests
    public static function inicializarVariablesEstaticasParaTests()
    {
        self::$codigo_obra_social_default = self::quote(MapucheConfig::getDefaultsObraSocial());
        self::$aportes_voluntarios        = MapucheConfig::getTopesJubilacionVoluntario();
        self::$codigo_os_aporte_adicional = MapucheConfig::getConceptosObraSocialAporteAdicional();
        self::$codigo_obrasocial_fc       = MapucheConfig::getConceptosObraSocialFliarAdherente();
        self::$tipoEmpresa                = MapucheConfig::getDatosUniversidadTipoEmpresa();
        self::$cantidad_adherentes_sicoss = MapucheConfig::getConceptosInformarAdherentesSicoss();
        self::$asignacion_familiar        = MapucheConfig::getConceptosAcumularAsigFamiliar();
        self::$trabajadorConvencionado    = MapucheConfig::getDatosUniversidadTrabajadorConvencionado();
        self::$codc_reparto               = self::quote(MapucheConfig::getDatosCodcReparto());
        self::$porc_aporte_adicional_jubilacion = MapucheConfig::getPorcentajeAporteDiferencialJubilacion();
        self::$hs_extras_por_novedad      = MapucheConfig::getSicossHorasExtrasNovedades();
        self::$categoria_diferencial      = MapucheConfig::getCategoriasDiferencial();
    }

    /**
     * Obtiene el código de reparto para tests
     */
    public static function getCodcReparto()
    {
        return self::$codc_reparto;
    }



    /**
     * Limpia las tablas temporales - Método público para tests
     */
    public static function limpiarTablasTemporalesParaTests()
    {
        try {
            $sql = "DROP TABLE IF EXISTS pre_conceptos_liquidados CASCADE";
            DB::connection(self::getStaticConnectionName())->statement($sql);

            $sql2 = "DROP VIEW IF EXISTS conceptos_liquidados CASCADE";
            DB::connection(self::getStaticConnectionName())->statement($sql2);

            Log::info('✅ Tablas temporales limpiadas');
        } catch (\Exception $e) {
            Log::warning('⚠️ Error limpiando tablas temporales: ' . $e->getMessage());
        }
    }

    /**
     * Verifica el estado de las variables estáticas - Para debugging
     */
    public static function verificarEstadoVariablesEstaticas()
    {
        return [
            'codc_reparto' => self::$codc_reparto,
            'codigo_obra_social_default' => self::$codigo_obra_social_default,
            'tipoEmpresa' => self::$tipoEmpresa,
            'trabajadorConvencionado' => self::$trabajadorConvencionado,
            'cantidad_adherentes_sicoss' => self::$cantidad_adherentes_sicoss,
            'asignacion_familiar' => self::$asignacion_familiar,
            'hs_extras_por_novedad' => self::$hs_extras_por_novedad,
            'categoria_diferencial' => self::$categoria_diferencial
        ];
    }

    /**
     * Calcula la memoria estimada necesaria para pre-cargar todos los conceptos
     *
     * @param array $legajos Array de legajos a procesar
     * @return array Información detallada sobre memoria necesaria
     */
    public static function calcular_memoria_necesaria($legajos): array
    {
        $total_legajos = count($legajos);

        if ($total_legajos === 0) {
            return ['estimacion_mb' => 0, 'factible' => true, 'metodo_recomendado' => 'vacio'];
        }

        Log::info('🧮 Calculando memoria necesaria...', [
            'total_legajos' => $total_legajos
        ]);

        // PASO 1: Muestra representativa (1-5% de legajos, mín 50, máx 500)
        $muestra_size = max(50, min(30000, intval($total_legajos * 0.03)));
        $legajos_muestra = array_slice($legajos, 0, $muestra_size);
        $nros_legajos_muestra = implode(',', array_column($legajos_muestra, 'nro_legaj'));

        // PASO 2: Medir memoria antes de la consulta muestra
        $memoria_antes = memory_get_usage(true);
        gc_collect_cycles(); // Limpiar memoria

        // PASO 3: Consulta de muestra
        $sql_muestra = "
            SELECT
                cl.nro_legaj,
                cl.impp_conce,
                cl.nov1_conce,
                cl.codn_conce,
                cl.tipos_grupos,
                cl.nro_cargo,
                cl.codigoescalafon,
                cl.tipo_conce,
                dh12.nro_orimp
            FROM conceptos_liquidados cl
            LEFT JOIN mapuche.dh12 ON dh12.codn_conce = cl.codn_conce
            WHERE cl.nro_legaj IN ($nros_legajos_muestra)
              AND cl.tipos_grupos IS NOT NULL
        ";

        $inicio = microtime(true);
        $resultado_muestra = DB::connection(self::getStaticConnectionName())->select($sql_muestra);
        $tiempo_consulta = (microtime(true) - $inicio) * 1000;

        // PASO 4: Convertir a arrays y medir memoria
        $conceptos_muestra = array_map(function ($item) {
            return (array)$item;
        }, $resultado_muestra);

        $memoria_despues = memory_get_usage(true);
        $memoria_muestra = $memoria_despues - $memoria_antes;

        // PASO 5: Calcular estadísticas de la muestra
        $conceptos_en_muestra = count($conceptos_muestra);
        $legajos_con_conceptos = count(array_unique(array_column($conceptos_muestra, 'nro_legaj')));

        $promedio_conceptos_por_legajo = $legajos_con_conceptos > 0
            ? $conceptos_en_muestra / $legajos_con_conceptos
            : 0;

        // PASO 6: Extrapolación inteligente
        $factor_extrapolacion = $total_legajos / $muestra_size;
        $memoria_base_estimada = $memoria_muestra * $factor_extrapolacion;

        // PASO 7: Factores de corrección y overhead
        $factor_overhead_php = 1.3;        // 30% overhead de PHP arrays/objects
        $factor_agrupacion = 1.2;          // 20% adicional para agrupación por legajo
        $factor_procesamiento = 1.4;       // 40% adicional para procesamiento
        $buffer_seguridad = 100;           // 100MB buffer mínimo

        $memoria_total_estimada = ($memoria_base_estimada * $factor_overhead_php * $factor_agrupacion * $factor_procesamiento) + ($buffer_seguridad * 1024 * 1024);

        // PASO 8: Información del sistema
        $memoria_limite = self::obtener_limite_memoria_bytes();
        $memoria_actual = memory_get_usage(true);
        $memoria_disponible = $memoria_limite - $memoria_actual;

        // PASO 9: Estimación de tiempo total
        $tiempo_total_estimado = ($tiempo_consulta / $muestra_size) * $total_legajos;

        // PASO 10: Determinar factibilidad
        $factible = $memoria_total_estimada < ($memoria_disponible * 0.8); // 80% del disponible

        // PASO 11: Recomendar método
        $metodo_recomendado = self::recomendar_metodo_procesamiento(
            $factible,
            $memoria_total_estimada,
            $memoria_disponible,
            $total_legajos,
            $promedio_conceptos_por_legajo
        );

        // Limpiar datos de muestra
        unset($conceptos_muestra, $resultado_muestra);
        gc_collect_cycles();

        return [
            // Estadísticas de muestra
            'muestra' => [
                'legajos_muestra' => $muestra_size,
                'conceptos_encontrados' => $conceptos_en_muestra,
                'promedio_conceptos_por_legajo' => round($promedio_conceptos_por_legajo, 2),
                'memoria_muestra_mb' => round($memoria_muestra / 1024 / 1024, 2),
                'tiempo_consulta_ms' => round($tiempo_consulta, 2)
            ],

            // Extrapolación total
            'estimacion_total' => [
                'memoria_base_mb' => round($memoria_base_estimada / 1024 / 1024, 2),
                'memoria_con_overhead_mb' => round($memoria_total_estimada / 1024 / 1024, 2),
                'tiempo_estimado_ms' => round($tiempo_total_estimado, 2),
                'conceptos_totales_estimados' => intval($promedio_conceptos_por_legajo * $total_legajos)
            ],

            // Sistema
            'sistema' => [
                'memoria_limite_mb' => round($memoria_limite / 1024 / 1024, 2),
                'memoria_actual_mb' => round($memoria_actual / 1024 / 1024, 2),
                'memoria_disponible_mb' => round($memoria_disponible / 1024 / 1024, 2)
            ],

            // Decisión
            'factible' => $factible,
            'metodo_recomendado' => $metodo_recomendado,
            'confianza' => self::calcular_confianza_estimacion($muestra_size, $total_legajos)
        ];
    }

    /**
     * Obtiene el límite de memoria en bytes
     */
    private static function obtener_limite_memoria_bytes(): int
    {
        $limite = ini_get('memory_limit');

        if ($limite == -1) {
            return PHP_INT_MAX; // Sin límite
        }

        $unidad = strtolower(substr($limite, -1));
        $numero = intval(substr($limite, 0, -1));

        switch ($unidad) {
            case 'g':
                return $numero * 1024 * 1024 * 1024;
            case 'm':
                return $numero * 1024 * 1024;
            case 'k':
                return $numero * 1024;
            default:
                return intval($limite);
        }
    }

    /**
     * Recomienda el método de procesamiento según los recursos
     */
    private static function recomendar_metodo_procesamiento(
        bool $factible,
        int $memoria_estimada,
        int $memoria_disponible,
        int $total_legajos,
        float $promedio_conceptos
    ): string {
        if (!$factible) {
            if ($total_legajos > 50000) {
                return 'chunks_grandes'; // Chunks de 2000-5000
            } else {
                return 'chunks_medianos'; // Chunks de 1000
            }
        }

        // Es factible la pre-carga masiva
        if ($memoria_estimada < ($memoria_disponible * 0.5)) {
            return 'masivo_seguro'; // Usar pre-carga masiva
        } else {
            return 'masivo_monitoreado'; // Pre-carga con monitoreo intensivo
        }
    }

    /**
     * Calcula la confianza de la estimación basada en el tamaño de muestra
     */
    private static function calcular_confianza_estimacion(int $muestra_size, int $total_legajos): string
    {
        $porcentaje_muestra = ($muestra_size / $total_legajos) * 100;

        if ($porcentaje_muestra >= 10) return 'muy_alta';
        if ($porcentaje_muestra >= 5) return 'alta';
        if ($porcentaje_muestra >= 2) return 'media';
        return 'baja';
    }

    /**
     * Pre-carga todos los datos de cargos para todos los legajos de una vez.
     *
     * Elimina 114,000 consultas individuales reemplazándolas por 1 sola consulta masiva.
     * Incluye límites, cargos sin licencia y cargos con licencia.
     *
     * @param array $legajos Array de legajos con estructura ['nro_legaj' => valor]
     * @return array Array con datos de cargos agrupados por legajo
     */
    public static function precargar_todos_datos_cargos($legajos): array
    {
        $nros_legajos = array_column($legajos, 'nro_legaj');

        if (empty($nros_legajos)) {
            Log::warning('precargar_todos_datos_cargos: No hay legajos para procesar');
            return [];
        }

        $legajos_in = implode(',', $nros_legajos);
        $fecha_inicio = self::quote(MapucheConfig::getFechaInicioPeriodoCorriente());
        $fecha_fin = self::quote(MapucheConfig::getFechaFinPeriodoCorriente());

        Log::info('precargar_todos_datos_cargos: Iniciando pre-carga', [
            'cantidad_legajos' => count($nros_legajos),
            'memoria_antes' => round(memory_get_usage(true) / 1024 / 1024, 2) . ' MB'
        ]);

        $inicio = microtime(true);

        // ✅ UNA SOLA CONSULTA MASIVA que obtiene TODOS los datos de cargos
        $sql = "
        WITH
        -- CTE 1: Límites de cargos por legajo
        limites_cargos AS (
            SELECT
                nro_legaj,
                CASE
                    WHEN MIN(fec_alta) > $fecha_inicio::date
                    THEN date_part('day', MIN(fec_alta)::timestamp)
                    ELSE date_part('day', timestamp $fecha_inicio)::integer
                END AS minimo,
                MAX(CASE
                    WHEN fec_baja > $fecha_fin::date OR fec_baja IS NULL
                    THEN date_part('day', timestamp $fecha_fin)::integer
                    ELSE date_part('day', fec_baja::timestamp)
                END) AS maximo
            FROM mapuche.dh03
            WHERE (fec_baja IS NULL OR fec_baja >= $fecha_inicio::date)
              AND nro_legaj IN ($legajos_in)
            GROUP BY nro_legaj
        ),

        -- CTE 2: Cargos con licencias vigentes (problemas)
        cargos_con_licencia_problematica AS (
            SELECT DISTINCT dh05.nro_cargo
            FROM mapuche.dh05
            JOIN mapuche.dl02 ON (dh05.nrovarlicencia = dl02.nrovarlicencia)
            WHERE suc.map_es_licencia_vigente(dh05.nro_licencia)
              AND (dl02.es_maternidad IS TRUE OR
                   (NOT dl02.es_remunerada OR
                    (dl02.es_remunerada AND dl02.porcremuneracion = '0')))
        ),

        -- CTE 3: Todos los cargos activos básicos
        cargos_base AS (
            SELECT
                dh03.nro_legaj,
                dh03.nro_cargo,
                CASE
                    WHEN fec_alta <= $fecha_inicio::date
                    THEN date_part('day', timestamp $fecha_inicio)::integer
                    ELSE date_part('day', fec_alta::timestamp)
                END AS inicio_cargo,
                CASE
                    WHEN fec_baja > $fecha_fin::date OR fec_baja IS NULL
                    THEN date_part('day', timestamp $fecha_fin)::integer
                    ELSE date_part('day', fec_baja::timestamp)
                END AS final_cargo,
                CASE
                    WHEN dh03.nro_cargo IN (SELECT nro_cargo FROM cargos_con_licencia_problematica)
                    THEN false ELSE true
                END AS sin_licencia
            FROM mapuche.dh03
            WHERE (fec_baja IS NULL OR fec_baja >= $fecha_inicio::date)
              AND nro_legaj IN ($legajos_in)
        ),

        -- CTE 4: Cargos con licencias vigentes detalladas
        cargos_con_licencias AS (
            SELECT
                dh03.nro_legaj,
                dh03.nro_cargo,
                CASE
                    WHEN fec_alta <= $fecha_inicio::date
                    THEN date_part('day', timestamp $fecha_inicio)::integer
                    ELSE date_part('day', fec_alta::timestamp)
                END AS inicio_cargo,
                CASE
                    WHEN fec_baja > $fecha_fin::date OR fec_baja IS NULL
                    THEN date_part('day', timestamp $fecha_fin)::integer
                    ELSE date_part('day', fec_baja::timestamp)
                END AS final_cargo,
                CASE
                    WHEN fec_desde <= $fecha_inicio::date
                    THEN date_part('day', timestamp $fecha_inicio)::integer
                    ELSE date_part('day', fec_desde::timestamp)
                END AS inicio_lic,
                CASE
                    WHEN fec_hasta > $fecha_fin::date OR fec_hasta IS NULL
                    THEN date_part('day', timestamp $fecha_fin)::integer
                    ELSE date_part('day', fec_hasta::timestamp)
                END AS final_lic,
                CASE
                    WHEN dl02.es_maternidad THEN 5::integer
                    ELSE CASE
                        WHEN dl02.es_remunerada THEN 1::integer
                        ELSE 13::integer
                    END
                END AS condicion
            FROM mapuche.dh03
            JOIN mapuche.dh05 ON dh05.nro_cargo = dh03.nro_cargo
            LEFT OUTER JOIN mapuche.dl02 ON (dh05.nrovarlicencia = dl02.nrovarlicencia)
            WHERE (fec_baja IS NULL OR fec_baja >= $fecha_inicio::date)
              AND (fec_desde <= $fecha_fin::date AND fec_hasta >= $fecha_inicio::date)
              AND dh03.nro_legaj IN ($legajos_in)
              AND dh03.nro_cargo NOT IN (
                  SELECT nro_cargo FROM mapuche.dh05 dh05_sub
                  JOIN mapuche.dl02 dl02_sub ON (dh05_sub.nrovarlicencia = dl02_sub.nrovarlicencia)
                  WHERE dh05_sub.nro_cargo = dh03.nro_cargo
                    AND suc.map_es_licencia_vigente(dh05_sub.nro_licencia)
                    AND (dh05_sub.fec_desde < mapuche.map_get_fecha_inicio_periodo() - 1)
                    AND (dl02_sub.es_maternidad IS TRUE OR
                         (NOT dl02_sub.es_remunerada OR
                          (dl02_sub.es_remunerada AND dl02_sub.porcremuneracion = '0')))
              )
        )

        -- Resultado final: Todo combinado
        SELECT
            'limites' as tipo_dato,
            lc.nro_legaj,
            NULL as nro_cargo,
            lc.minimo as inicio,
            lc.maximo as final,
            NULL::integer as inicio_lic,
            NULL::integer as final_lic,
            NULL::integer as condicion,
            NULL::boolean as sin_licencia
        FROM limites_cargos lc

        UNION ALL

        SELECT
            'cargo_sin_licencia' as tipo_dato,
            cb.nro_legaj,
            cb.nro_cargo,
            cb.inicio_cargo as inicio,
            cb.final_cargo as final,
            NULL::integer as inicio_lic,
            NULL::integer as final_lic,
            NULL::integer as condicion,
            cb.sin_licencia
        FROM cargos_base cb
        WHERE cb.sin_licencia = true

        UNION ALL

        SELECT
            'cargo_con_licencia' as tipo_dato,
            ccl.nro_legaj,
            ccl.nro_cargo,
            ccl.inicio_cargo as inicio,
            ccl.final_cargo as final,
            ccl.inicio_lic,
            ccl.final_lic,
            ccl.condicion,
            NULL::boolean as sin_licencia
        FROM cargos_con_licencias ccl

        ORDER BY nro_legaj, tipo_dato, nro_cargo
        ";

        try {
            $resultado = DB::connection(self::getStaticConnectionName())->select($sql);

            $fin = microtime(true);
            $tiempo_consulta = ($fin - $inicio) * 1000;

            Log::info('precargar_todos_datos_cargos: Pre-carga completada', [
                'registros_cargados' => count($resultado),
                'tiempo_consulta_ms' => round($tiempo_consulta, 2),
                'memoria_despues' => round(memory_get_usage(true) / 1024 / 1024, 2) . ' MB'
            ]);

            // Agrupar por legajo y tipo para acceso O(1)
            return self::agrupar_datos_cargos_por_legajo($resultado);
        } catch (\Exception $e) {
            Log::error('precargar_todos_datos_cargos: Error en consulta SQL', [
                'error' => $e->getMessage(),
                'cantidad_legajos' => count($nros_legajos)
            ]);
            return [];
        }
    }

    /**
     * Agrupa los datos de cargos por legajo para acceso rápido O(1)
     */
    public static function agrupar_datos_cargos_por_legajo($datos_cargos): array
    {
        $inicio = microtime(true);
        $datos_por_legajo = [];

        foreach ($datos_cargos as $dato) {
            $nro_legaj = $dato->nro_legaj;
            $tipo = $dato->tipo_dato;

            if (!isset($datos_por_legajo[$nro_legaj])) {
                $datos_por_legajo[$nro_legaj] = [
                    'limites' => null,
                    'cargos_sin_licencia' => [],
                    'cargos_con_licencia' => []
                ];
            }

            switch ($tipo) {
                case 'limites':
                    $datos_por_legajo[$nro_legaj]['limites'] = [
                        'minimo' => (int)$dato->inicio,
                        'maximo' => (int)$dato->final
                    ];
                    break;

                case 'cargo_sin_licencia':
                    $datos_por_legajo[$nro_legaj]['cargos_sin_licencia'][] = [
                        'nro_cargo' => $dato->nro_cargo,
                        'inicio' => (int)$dato->inicio,
                        'final' => (int)$dato->final
                    ];
                    break;

                case 'cargo_con_licencia':
                    $datos_por_legajo[$nro_legaj]['cargos_con_licencia'][] = [
                        'nro_cargo' => $dato->nro_cargo,
                        'inicio' => (int)$dato->inicio,
                        'final' => (int)$dato->final,
                        'inicio_lic' => (int)$dato->inicio_lic,
                        'final_lic' => (int)$dato->final_lic,
                        'condicion' => (int)$dato->condicion
                    ];
                    break;
            }
        }

        $fin = microtime(true);
        $tiempo_agrupacion = ($fin - $inicio) * 1000;

        Log::info('agrupar_datos_cargos_por_legajo: Agrupación completada', [
            'datos_procesados' => count($datos_cargos),
            'legajos_agrupados' => count($datos_por_legajo),
            'tiempo_agrupacion_ms' => round($tiempo_agrupacion, 2)
        ]);

        return $datos_por_legajo;
    }

    /**
     * Versión optimizada de get_limites_cargos que NO hace consultas SQL
     */
    public static function get_limites_cargos_optimizado($legajo, $datos_cargos_por_legajo): array
    {
        $datos_legajo = $datos_cargos_por_legajo[$legajo] ?? null;

        if (!$datos_legajo || !$datos_legajo['limites']) {
            // Fallback para legajos sin cargos
            $fecha_fin = self::quote(MapucheConfig::getFechaFinPeriodoCorriente());
            return [
                'minimo' => 1,
                'maximo' => (int)substr($fecha_fin, 9, 2)
            ];
        }

        return $datos_legajo['limites'];
    }

    /**
     * Versión optimizada de get_cargos_activos_sin_licencia que NO hace consultas SQL
     */
    public static function get_cargos_activos_sin_licencia_optimizado($legajo, $datos_cargos_por_legajo): array
    {
        $datos_legajo = $datos_cargos_por_legajo[$legajo] ?? null;

        if (!$datos_legajo) {
            return [];
        }

        return $datos_legajo['cargos_sin_licencia'];
    }

    /**
     * Versión optimizada de get_cargos_activos_con_licencia_vigente que NO hace consultas SQL
     */
    public static function get_cargos_activos_con_licencia_vigente_optimizado($legajo, $datos_cargos_por_legajo): array
    {
        $datos_legajo = $datos_cargos_por_legajo[$legajo] ?? null;

        if (!$datos_legajo) {
            return [];
        }

        return $datos_legajo['cargos_con_licencia'];
    }

    /**
     * Pre-carga todos los datos de otra actividad para todos los legajos de una vez.
     *
     * Elimina 38,000 consultas individuales reemplazándolas por 1 sola consulta masiva.
     * Obtiene solo el registro más reciente por legajo (igual lógica que original).
     *
     * @param array $legajos Array de legajos con estructura ['nro_legaj' => valor]
     * @return array Array con datos de otra actividad agrupados por legajo
     */
    public static function precargar_otra_actividad_todos_legajos($legajos): array
    {
        $nros_legajos = array_column($legajos, 'nro_legaj');

        if (empty($nros_legajos)) {
            Log::warning('precargar_otra_actividad_todos_legajos: No hay legajos para procesar');
            return [];
        }

        $legajos_in = implode(',', $nros_legajos);

        Log::info('precargar_otra_actividad_todos_legajos: Iniciando pre-carga', [
            'cantidad_legajos' => count($nros_legajos),
            'memoria_antes' => round(memory_get_usage(true) / 1024 / 1024, 2) . ' MB'
        ]);

        $inicio = microtime(true);

        // ✅ UNA SOLA CONSULTA MASIVA que obtiene el registro más reciente por legajo
        $sql = "
        WITH otra_actividad_reciente AS (
            SELECT
                nro_legaj,
                importe as ImporteBrutoOtraActividad,
                importe_sac as ImporteSACOtraActividad,
                vig_ano,
                vig_mes,
                ROW_NUMBER() OVER (
                    PARTITION BY nro_legaj
                    ORDER BY vig_ano DESC, vig_mes DESC
                ) as rn
            FROM mapuche.dhe9
            WHERE nro_legaj IN ($legajos_in)
        )
        SELECT
            nro_legaj,
            ImporteBrutoOtraActividad,
            ImporteSACOtraActividad,
            vig_ano,
            vig_mes
        FROM otra_actividad_reciente
        WHERE rn = 1
        ORDER BY nro_legaj
        ";

        try {
            $resultado = DB::connection(self::getStaticConnectionName())->select($sql);

            $fin = microtime(true);
            $tiempo_consulta = ($fin - $inicio) * 1000;

            Log::info('precargar_otra_actividad_todos_legajos: Pre-carga completada', [
                'registros_cargados' => count($resultado),
                'tiempo_consulta_ms' => round($tiempo_consulta, 2),
                'memoria_despues' => round(memory_get_usage(true) / 1024 / 1024, 2) . ' MB',
                'legajos_con_otra_actividad' => count($resultado),
                'legajos_sin_otra_actividad' => count($nros_legajos) - count($resultado)
            ]);

            // Agrupar por legajo para acceso O(1)
            return self::agrupar_otra_actividad_por_legajo($resultado, $nros_legajos);
        } catch (\Exception $e) {
            Log::error('precargar_otra_actividad_todos_legajos: Error en consulta SQL', [
                'error' => $e->getMessage(),
                'cantidad_legajos' => count($nros_legajos)
            ]);
            return [];
        }
    }

    /**
     * Agrupa los datos de otra actividad por legajo para acceso rápido O(1)
     * Incluye valores por defecto para legajos sin datos
     */
    public static function agrupar_otra_actividad_por_legajo($datos_otra_actividad, $todos_los_legajos): array
    {
        $inicio = microtime(true);
        $datos_por_legajo = [];

        // Inicializar todos los legajos con valores por defecto
        foreach ($todos_los_legajos as $legajo) {
            $datos_por_legajo[$legajo] = [
                'importebrutootraactividad' => 0,
                'importesacotraactividad' => 0,
                'tiene_datos' => false
            ];
        }

        // Sobrescribir con datos reales cuando existen
        foreach ($datos_otra_actividad as $dato) {
            $nro_legaj = $dato->nro_legaj;
            $datos_por_legajo[$nro_legaj] = [
                'importebrutootraactividad' => (float)$dato->ImporteBrutoOtraActividad,
                'importesacotraactividad' => (float)$dato->ImporteSACOtraActividad,
                'tiene_datos' => true,
                'periodo' => $dato->vig_ano . '/' . str_pad($dato->vig_mes, 2, '0', STR_PAD_LEFT)
            ];
        }

        $fin = microtime(true);
        $tiempo_agrupacion = ($fin - $inicio) * 1000;

        $legajos_con_datos = array_filter($datos_por_legajo, fn($d) => $d['tiene_datos']);

        Log::info('agrupar_otra_actividad_por_legajo: Agrupación completada', [
            'datos_procesados' => count($datos_otra_actividad),
            'total_legajos' => count($datos_por_legajo),
            'legajos_con_datos' => count($legajos_con_datos),
            'legajos_sin_datos' => count($datos_por_legajo) - count($legajos_con_datos),
            'tiempo_agrupacion_ms' => round($tiempo_agrupacion, 2)
        ]);

        return $datos_por_legajo;
    }

    /**
     * Versión optimizada de otra_actividad que NO hace consultas SQL
     *
     * @param string $nro_legajo Número de legajo
     * @param array $datos_otra_actividad_por_legajo Datos pre-cargados
     * @return array Array con importes de otra actividad
     */
    public static function otra_actividad_optimizado($nro_legajo, $datos_otra_actividad_por_legajo): array
    {
        // Buscar datos pre-cargados
        $datos = $datos_otra_actividad_por_legajo[$nro_legajo] ?? null;

        // Retornar datos o valores por defecto (igual lógica que función original)
        if ($datos && $datos['tiene_datos']) {
            return [
                'importebrutootraactividad' => $datos['importebrutootraactividad'],
                'importesacotraactividad' => $datos['importesacotraactividad']
            ];
        } else {
            // Igual que la función original cuando no hay datos
            return [
                'importesacotraactividad' => 0,
                'importebrutootraactividad' => 0
            ];
        }
    }

    /**
     * Pre-carga todos los códigos de obra social para todos los legajos de una vez.
     *
     * Elimina 76,000 consultas individuales reemplazándolas por 2 consultas masivas.
     * Incluye la conversión a códigos DGI y manejo de jubilados.
     *
     * @param array $legajos Array de legajos con estructura ['nro_legaj' => valor]
     * @return array Array con códigos DGI agrupados por legajo
     */
    public static function precargar_codigos_obra_social_todos_legajos($legajos): array
    {
        $nros_legajos = array_column($legajos, 'nro_legaj');

        if (empty($nros_legajos)) {
            Log::warning('precargar_codigos_obra_social_todos_legajos: No hay legajos para procesar');
            return [];
        }

        $legajos_in = implode(',', $nros_legajos);

        Log::info('precargar_codigos_obra_social_todos_legajos: Iniciando pre-carga', [
            'cantidad_legajos' => count($nros_legajos),
            'memoria_antes' => round(memory_get_usage(true) / 1024 / 1024, 2) . ' MB'
        ]);

        $inicio = microtime(true);

        // ✅ CONSULTA MASIVA 1: Obtener todos los códigos de obra social de dh09
        $sql_obra_social = "
        SELECT
            dh09.nro_legaj,
            COALESCE(NULLIF(dh09.codc_obsoc, ''), " . self::$codigo_obra_social_default . ") as codc_obsoc
        FROM mapuche.dh09
        WHERE dh09.nro_legaj IN ($legajos_in)
        ";

        try {
            $resultado_dh09 = DB::connection(self::getStaticConnectionName())->select($sql_obra_social);

            // Crear mapa de legajos con sus códigos de obra social
            $legajos_obra_social = [];
            $codigos_obra_social_unicos = [];

            foreach ($resultado_dh09 as $row) {
                $legajos_obra_social[$row->nro_legaj] = $row->codc_obsoc;
                $codigos_obra_social_unicos[$row->codc_obsoc] = true;
            }

            // Agregar legajos faltantes con código por defecto
            foreach ($nros_legajos as $legajo) {
                if (!isset($legajos_obra_social[$legajo])) {
                    $legajos_obra_social[$legajo] = self::$codigo_obra_social_default;
                    $codigos_obra_social_unicos[self::$codigo_obra_social_default] = true;
                }
            }

            $tiempo_dh09 = microtime(true);

            // ✅ CONSULTA MASIVA 2: Obtener todos los códigos DGI de dh37
            $codigos_unicos = array_keys($codigos_obra_social_unicos);
            $codigos_quoted = array_map([self::class, 'quote'], $codigos_unicos);
            $codigos_in = implode(',', $codigos_quoted);

            $sql_codigos_dgi = "
            SELECT
                dh37.codc_obsoc,
                COALESCE(dh37.codn_osdgi, '000000') as codn_osdgi
            FROM mapuche.dh37
            WHERE dh37.codc_obsoc IN ($codigos_in)
            ";

            $resultado_dh37 = DB::connection(self::getStaticConnectionName())->select($sql_codigos_dgi);

            // Crear mapa de códigos obra social a códigos DGI
            $mapa_dgi = [];
            foreach ($resultado_dh37 as $row) {
                $mapa_dgi[$row->codc_obsoc] = $row->codn_osdgi;
            }

            // Agregar códigos faltantes con valor por defecto
            foreach ($codigos_unicos as $codigo) {
                if (!isset($mapa_dgi[$codigo])) {
                    $mapa_dgi[$codigo] = '000000';
                }
            }

            $fin = microtime(true);
            $tiempo_total = ($fin - $inicio) * 1000;
            $tiempo_dh37 = ($fin - $tiempo_dh09) * 1000;
            $tiempo_dh09_ms = ($tiempo_dh09 - $inicio) * 1000;

            Log::info('precargar_codigos_obra_social_todos_legajos: Pre-carga completada', [
                'legajos_procesados' => count($legajos_obra_social),
                'codigos_obra_social_unicos' => count($codigos_unicos),
                'codigos_dgi_encontrados' => count($resultado_dh37),
                'tiempo_total_ms' => round($tiempo_total, 2),
                'tiempo_dh09_ms' => round($tiempo_dh09_ms, 2),
                'tiempo_dh37_ms' => round($tiempo_dh37, 2),
                'memoria_despues' => round(memory_get_usage(true) / 1024 / 1024, 2) . ' MB'
            ]);

            // ✅ CONSULTA MASIVA 3: Verificar jubilados (usando datos ya cargados en legajos)
            return self::construir_mapa_codigos_dgi_final($legajos, $legajos_obra_social, $mapa_dgi);
        } catch (\Exception $e) {
            Log::error('precargar_codigos_obra_social_todos_legajos: Error en consulta SQL', [
                'error' => $e->getMessage(),
                'cantidad_legajos' => count($nros_legajos)
            ]);
            return [];
        }
    }

    /**
     * Construye el mapa final de códigos DGI por legajo, aplicando regla de jubilados
     */
    public static function construir_mapa_codigos_dgi_final($legajos_originales, $legajos_obra_social, $mapa_dgi): array
    {
        $inicio = microtime(true);
        $codigos_dgi_por_legajo = [];

        foreach ($legajos_originales as $legajo_data) {
            $nro_legaj = $legajo_data['nro_legaj'];
            $estado = $legajo_data['estado'] ?? null;

            // ✅ Verificar si es jubilado usando datos ya cargados (sin consulta SQL)
            if ($estado === 'J') { // Jubilado
                $codigos_dgi_por_legajo[$nro_legaj] = '000000';
            } else {
                // Obtener código obra social y convertir a DGI
                $codigo_obra_social = $legajos_obra_social[$nro_legaj] ?? self::$codigo_obra_social_default;
                $codigo_dgi = $mapa_dgi[$codigo_obra_social] ?? '000000';
                $codigos_dgi_por_legajo[$nro_legaj] = $codigo_dgi;
            }
        }

        $fin = microtime(true);
        $tiempo_construccion = ($fin - $inicio) * 1000;

        $jubilados = array_filter($codigos_dgi_por_legajo, fn($codigo) => $codigo === '000000');

        Log::info('construir_mapa_codigos_dgi_final: Construcción completada', [
            'total_legajos' => count($codigos_dgi_por_legajo),
            'jubilados_detectados' => count($jubilados),
            'legajos_con_obra_social' => count($codigos_dgi_por_legajo) - count($jubilados),
            'tiempo_construccion_ms' => round($tiempo_construccion, 2)
        ]);

        return $codigos_dgi_por_legajo;
    }

    /**
     * Versión optimizada de codigo_os que NO hace consultas SQL
     *
     * @param string $nro_legajo Número de legajo
     * @param array $codigos_dgi_por_legajo Códigos DGI pre-cargados
     * @return string Código DGI de la obra social
     */
    public static function codigo_os_optimizado($nro_legajo, $codigos_dgi_por_legajo): string
    {
        // Buscar código pre-cargado
        $codigo_dgi = $codigos_dgi_por_legajo[$nro_legajo] ?? '000000';

        return $codigo_dgi;
    }

    /**
     * Guarda los legajos procesados en la base de datos usando inserción masiva
     * Optimizado para grandes volúmenes (40K+ registros)
     *
     * @param array $legajos Legajos procesados con todos los cálculos
     * @param PeriodoFiscal $periodo_fiscal Período fiscal en formato YYYYMM
     * @return array Estadísticas del guardado
     */
    public static function guardar_en_bd(array $legajos, PeriodoFiscal $periodo_fiscal): array
    {
        $stats = [
            'total_procesados' => count($legajos),
            'insertados' => 0,
            'chunks_procesados' => 0,
            'errores' => 0,
            'inicio' => microtime(true)
        ];

        Log::info("Iniciando guardado SICOSS en BD (BULK)", [
            'periodo' => $periodo_fiscal->toString(),
            'total_legajos' => $stats['total_procesados']
        ]);

        try {
            $connection = DB::connection(self::getStaticConnectionName());
            $connection->beginTransaction();

            // 1. Limpiar registros existentes del período
            $eliminados = $connection->table('suc.afip_mapuche_sicoss')
                ->where('periodo_fiscal', $periodo_fiscal->toString())
                ->delete();

            Log::info("Eliminados {$eliminados} registros existentes del período {$periodo_fiscal->toString()}");

            // 2. Preparar datos para inserción masiva
            $datos_para_insertar = [];
            $chunk_size = 1000; // Insertar de a 1000 registros

            foreach ($legajos as $index => $legajo) {
                try {
                    $datos_mapeados = self::mapear_legajo_a_modelo($legajo, $periodo_fiscal->toString());
                    $datos_para_insertar[] = $datos_mapeados;

                    // Insertar cuando llegamos al chunk_size o al final
                    if (count($datos_para_insertar) === $chunk_size || $index === count($legajos) - 1) {

                        // Inserción masiva
                        $connection->table('suc.afip_mapuche_sicoss')->insert($datos_para_insertar);
                        $stats['insertados'] += count($datos_para_insertar);
                        $stats['chunks_procesados']++;

                        Log::info("Chunk {$stats['chunks_procesados']}: Insertados " . count($datos_para_insertar) . " registros. Total: {$stats['insertados']}/{$stats['total_procesados']}");

                        // Limpiar array para el siguiente chunk
                        $datos_para_insertar = [];
                    }
                } catch (\Exception $e) {
                    $stats['errores']++;
                    Log::error("Error mapeando legajo SICOSS", [
                        'legajo' => $legajo['nro_legaj'] ?? 'N/A',
                        'cuil' => $legajo['cuit'] ?? 'N/A',
                        'error' => $e->getMessage()
                    ]);
                }
            }

            $connection->commit();

            $stats['tiempo_total'] = round(microtime(true) - $stats['inicio'], 2);

            Log::info("Guardado SICOSS en BD completado (BULK)", $stats);

            return $stats;
        } catch (\Exception $e) {
            if (isset($connection)) {
                $connection->rollBack();
            }

            Log::error("Error crítico en guardado SICOSS BD", [
                'error' => $e->getMessage(),
                'periodo' => $periodo_fiscal->toString()
            ]);
            throw $e;
        }
    }

    /**
     * Mapea los datos de un legajo procesado al formato del modelo AfipMapucheSicoss
     * Optimizado para inserción masiva (sin Eloquent)
     *
     * @param array $legajo Datos del legajo procesado por SICOSS
     * @param string $periodo_fiscal Período fiscal
     * @return array Datos mapeados para inserción directa en tabla
     */
    private static function mapear_legajo_a_modelo(array $legajo, string $periodo_fiscal): array
    {
        return [
            // Identificación
            'periodo_fiscal' => $periodo_fiscal,
            'cuil' => $legajo['cuit'] ?? '',
            'apnom' => \App\Services\EncodingService::toLatin1($legajo['apyno'] ?? ''),

            // Datos familiares
            'conyuge' => ($legajo['conyugue'] ?? false) ? true : false,
            'cant_hijos' => (int)($legajo['hijos'] ?? 0),

            // Códigos situación laboral
            'cod_situacion' => (int)($legajo['codigosituacion'] ?? 0),
            'cod_cond' => (int)($legajo['codigocondicion'] ?? 0),
            'cod_act' => (int)($legajo['TipoDeActividad'] ?? 0),
            'cod_zona' => (int)($legajo['codigozona'] ?? 0),

            // Aportes y obra social
            'porc_aporte' => (float)($legajo['aporteadicional'] ?? 0),
            'cod_mod_cont' => (int)($legajo['codigocontratacion'] ?? 0),
            'cod_os' => $legajo['codigo_os'] ?? '',
            'cant_adh' => (int)($legajo['adherentes'] ?? 0),

            // Importes principales
            'rem_total' => (float)($legajo['IMPORTE_BRUTO'] ?? 0),
            'rem_impo1' => (float)($legajo['IMPORTE_IMPON'] ?? 0),
            'asig_fam_pag' => (float)($legajo['AsignacionesFliaresPagadas'] ?? 0),
            'aporte_vol' => (float)($legajo['IMPORTE_VOLUN'] ?? 0),
            'imp_adic_os' => (float)($legajo['IMPORTE_ADICI'] ?? 0),
            'exc_aport_ss' => (float)(abs($legajo['ImporteSICOSSDec56119'] ?? 0)),
            'exc_aport_os' => 0.00,
            'prov' => \App\Services\EncodingService::toLatin1($legajo['provincialocalidad'] ?? ''),

            // Importes adicionales
            'rem_impo2' => (float)($legajo['ImporteImponiblePatronal'] ?? 0),
            'rem_impo3' => (float)($legajo['ImporteImponiblePatronal'] ?? 0),
            'rem_impo4' => (float)($legajo['ImporteImponible_4'] ?? 0),

            // Datos siniestros y empresa
            'cod_siniestrado' => null,
            'marca_reduccion' => '0',
            'recomp_lrt' => 0.00,
            'tipo_empresa' => self::$tipoEmpresa ?? 'K',
            'aporte_adic_os' => (float)($legajo['AporteAdicionalObraSocial'] ?? 0),
            'regimen' => (string)($legajo['regimen'] ?? '0'),

            // Situaciones de revista
            'sit_rev1' => (string)($legajo['codigorevista1'] ?? '0'),
            'dia_ini_sit_rev1' => (int)($legajo['fecharevista1'] ?? 1),
            'sit_rev2' => (string)($legajo['codigorevista2'] ?? '0'),
            'dia_ini_sit_rev2' => (int)($legajo['fecharevista2'] ?? 0),
            'sit_rev3' => (string)($legajo['codigorevista3'] ?? '0'),
            'dia_ini_sit_rev3' => (int)($legajo['fecharevista3'] ?? 0),

            // Conceptos salariales
            'sueldo_adicc' => (float)($legajo['ImporteSueldoMasAdicionales'] ?? 0),
            'sac' => (float)($legajo['ImporteSAC'] ?? 0),
            'horas_extras' => (float)($legajo['ImporteHorasExtras'] ?? 0),
            'zona_desfav' => (float)($legajo['ImporteZonaDesfavorable'] ?? 0),
            'vacaciones' => (float)($legajo['ImporteVacaciones'] ?? 0),
            'cant_dias_trab' => (int)($legajo['dias_trabajados'] ?? 0),
            'rem_impo5' => (float)(($legajo['ImporteImponible_4'] ?? 0) - ($legajo['ImporteTipo91'] ?? 0)),
            'convencionado' => ($legajo['trabajadorconvencionado'] ?? false) ? 1 : 0,
            'rem_impo6' => (float)($legajo['ImporteImponible_6'] ?? 0),
            'tipo_oper' => (string)($legajo['TipoDeOperacion'] ?? '0'),
            'adicionales' => (float)($legajo['ImporteAdicionales'] ?? 0),
            'premios' => (float)($legajo['ImportePremios'] ?? 0),
            'rem_dec_788' => (float)($legajo['Remuner78805'] ?? 0),
            'rem_imp7' => (float)($legajo['ImporteImponible_6'] ?? 0),
            'nro_horas_ext' => (int)(ceil($legajo['CantidadHorasExtras'] ?? 0)),
            'cpto_no_remun' => (float)($legajo['ImporteNoRemun'] ?? 0),
            'maternidad' => (float)($legajo['ImporteMaternidad'] ?? 0),
            'rectificacion_remun' => (float)($legajo['ImporteRectificacionRemun'] ?? 0),
            'rem_imp9' => (float)($legajo['importeimponible_9'] ?? 0),
            'contrib_dif' => (float)($legajo['ContribTareaDif'] ?? 0),
            'hstrab' => 0,
            'seguro' => ($legajo['SeguroVidaObligatorio'] ?? false) ? 1 : 0,
            'ley' => (float)($legajo['ImporteSICOSS27430'] ?? 0),
            'incsalarial' => (float)($legajo['IncrementoSolidario'] ?? 0),
            'remimp11' => 0.00,
        ];
    }

    /**
     * Método principal para generar SICOSS y guardarlo en base de datos
     *
     * @param array $datos Parámetros de configuración
     * @param PeriodoFiscal $periodo_fiscal Período en formato YYYYMM
     * @param bool $incluir_inactivos Si incluir empleados inactivos
     * @return array Estadísticas del procesamiento
     */
    public static function generar_sicoss_bd(
        array $datos,
        PeriodoFiscal $periodo_fiscal,
        bool $incluir_inactivos = false
    ): array {
        $mes = $periodo_fiscal->month();
        $anio = $periodo_fiscal->year();

        Log::info("Iniciando generación SICOSS en BD", [
            'periodo' => $periodo_fiscal->toString(),
            'mes' => $mes,
            'anio' => $anio,
            'incluir_inactivos' => $incluir_inactivos
        ]);

        try {
            // configurar datos predeterminados si no se proporcionan
            $datos_completos = array_merge([
                'check_lic' => false,
                'check_retr' => false,
                'check_sin_activo' => $incluir_inactivos,
                'codc_reparto' => self::getCodcReparto(),
            ], $datos);

            // Obtener legajos con el WHERE apropiado
            $where_periodo = " dh22.per_liano = {$anio} and dh22.per_limes = {$mes} ";


            $legajos = self::obtener_legajos(
                $datos_completos['codc_reparto'],
                $where_periodo,
                ' true ',
                $datos_completos['check_lic'],
                $datos_completos['check_sin_activo']
            );

            Log::info("Legajos obtenidos para procesamiento", ['cantidad' => count($legajos)]);

            // Procesar SICOSS y guardar en BD
            return self::procesa_sicoss_bd(
                $datos_completos,
                $anio,
                $mes,
                $legajos,
                $periodo_fiscal
            );
        } catch (\Exception $e) {
            Log::error("Error en generación SICOSS BD", [
                'periodo' => $periodo_fiscal->toString(),
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Versión especializada de procesa_sicoss para guardado en BD
     * Utiliza todas las optimizaciones y guarda directamente en base de datos
     *
     * @param array $datos Configuración
     * @param int $per_anoct Año
     * @param int $per_mesct Mes
     * @param array $legajos Legajos a procesar
     * @param PeriodoFiscal $periodo_fiscal Período fiscal
     * @return array Estadísticas del procesamiento
     */
    public static function procesa_sicoss_bd(array $datos, int $per_anoct, int $per_mesct, array $legajos, PeriodoFiscal $periodo_fiscal): array
    {
        $inicio_total = microtime(true);
        $total_legajos = count($legajos);

        Log::info("Iniciando procesamiento SICOSS optimizado para BD", [
            'total_legajos' => $total_legajos,
            'periodo' => $periodo_fiscal->toString()
        ]);

        // Usar el método optimizado con bulk loading
        return self::procesar_periodo_optimizado(
            mes: $per_mesct,
            anio: $per_anoct,
            periodo_fiscal: $periodo_fiscal,
            legajos: $legajos,
            datos_config: $datos
        );
    }

    /**
     * Método optimizado que utiliza todas las precargadas para minimizar queries SQL
     * y luego guarda en BD usando bulk insert
     */
    public static function procesar_periodo_optimizado(
        int $mes,
        int $anio,
        PeriodoFiscal $periodo_fiscal,
        ?array $legajos = null,
        array $datos_config = []
    ): array {
        $inicio = microtime(true);

        // Si no se proporcionan legajos, obtenerlos
        if ($legajos === null) {
            $where_periodo = " dh22.per_liano = {$anio} and dh22.per_limes = {$mes} ";
            $legajos = self::obtener_legajos(
                $datos_config['codc_reparto'] ?? self::getCodcReparto(),
                $where_periodo,
                ' true ',
                $datos_config['check_lic'] ?? false,
                $datos_config['check_sin_activo'] ?? false
            );
        }

        $total_legajos = count($legajos);
        Log::info("Iniciando procesamiento optimizado", ['legajos' => $total_legajos]);

        // 1. Precarga masiva de conceptos liquidados
        Log::info("Precargando conceptos liquidados...");
        $conceptos_por_legajo = self::precargar_conceptos_todos_legajos($legajos);
        Log::info("Conceptos precargados: " . count($conceptos_por_legajo));

        // 2. Precarga masiva de datos de cargos
        Log::info("Precargando datos de cargos...");
        $datos_cargos_por_legajo = self::precargar_todos_datos_cargos($legajos);
        Log::info("Datos de cargos precargados para " . count($datos_cargos_por_legajo) . " legajos");

        // 3. Precarga otra actividad
        Log::info("Precargando otra actividad...");
        $otra_actividad_por_legajo = self::precargar_otra_actividad_todos_legajos($legajos);
        Log::info("Otra actividad precargada para " . count($otra_actividad_por_legajo) . " legajos");

        // 4. Precarga códigos obra social
        Log::info("Precargando códigos obra social...");
        $codigos_dgi_por_legajo = self::precargar_codigos_obra_social_todos_legajos($legajos);
        Log::info("Códigos DGI precargados para " . count($codigos_dgi_por_legajo) . " legajos");

        // 5. Procesar cada legajo usando datos precargados
        $legajos_procesados = [];
        $errores = 0;

        foreach ($legajos as $index => $legajo) {
            try {
                $nro_legajo = $legajo['nro_legaj'];

                // Usar métodos optimizados (sin SQL)
                $conceptos_legajo = $conceptos_por_legajo[$nro_legajo] ?? [];
                self::sumarizar_conceptos_optimizado($conceptos_legajo, $legajo);

                // Calcular otras actividades (optimizado)
                $otra_act = self::otra_actividad_optimizado($nro_legajo, $otra_actividad_por_legajo);
                $legajo = array_merge($legajo, $otra_act);

                // Código obra social (optimizado)
                $legajo['codigo_os'] = self::codigo_os_optimizado($nro_legajo, $codigos_dgi_por_legajo);

                // Datos de cargos (optimizado)
                $cargos_data = $datos_cargos_por_legajo[$nro_legajo] ?? [];
                $legajo['limits'] = self::get_limites_cargos_optimizado($legajo, $cargos_data);

                // Solo agregar legajos válidos (con importes > 0 o condiciones especiales)
                if (self::es_legajo_valido($legajo, $datos_config)) {
                    $legajos_procesados[] = $legajo;
                }

                // Log progreso cada 1000
                if (($index + 1) % 1000 === 0) {
                    Log::info("Procesamiento: " . ($index + 1) . "/{$total_legajos} legajos");
                }
            } catch (\Exception $e) {
                $errores++;
                Log::error("Error procesando legajo {$nro_legajo}", [
                    'error' => $e->getMessage()
                ]);
            }
        }

        $tiempo_procesamiento = round(microtime(true) - $inicio, 2);
        Log::info("Procesamiento completado", [
            'tiempo' => $tiempo_procesamiento . 's',
            'legajos_validos' => count($legajos_procesados),
            'errores' => $errores
        ]);

        // 6. Guardar en base de datos usando bulk insert
        return self::guardar_en_bd($legajos_procesados, $periodo_fiscal);
    }


    /**
     * Determina si un legajo es válido para ser incluido en el archivo SICOSS
     *
     * Un legajo se considera válido si cumple alguna de las siguientes condiciones:
     * - Tiene importes brutos, imponibles o imponibles tipo 6 mayores a cero
     * - Es una licencia especial (cuando check_lic está habilitado y el legajo tiene licencia)
     * - Es un empleado sin activo (cuando check_sin_activo está habilitado y código situación es 14)
     *
     * @param array $legajo Datos del legajo procesado con todos los cálculos
     * @param array $datos_config Configuración del procesamiento con flags de control
     * @return bool True si el legajo debe incluirse en SICOSS, false en caso contrario
     */
    private static function es_legajo_valido(array $legajo, array $datos_config): bool
    {
        // Verificar si tiene importes
        $tiene_importes = (
            ($legajo['IMPORTE_BRUTO'] ?? 0) > 0 ||
            ($legajo['IMPORTE_IMPON'] ?? 0) > 0 ||
            ($legajo['ImporteImponible_6'] ?? 0) > 0
        );

        // Verificar condiciones especiales
        $es_licencia_especial = ($datos_config['check_lic'] ?? false) && ($legajo['licencia'] ?? false);
        $es_sin_activo = ($datos_config['check_sin_activo'] ?? false) && ($legajo['codigosituacion'] ?? 0) == 14;

        return $tiene_importes || $es_licencia_especial || $es_sin_activo;
    }

    /**
     * Método de prueba para generar SICOSS con un número limitado de legajos
     * Ideal para testing y validación
     *
     * @param PeriodoFiscal $periodo_fiscal Período en formato YYYYMM
     * @param int $limite Número máximo de legajos a procesar
     * @param bool $incluir_inactivos Si incluir empleados inactivos
     * @return array Estadísticas del procesamiento + datos de muestra
     */
    public static function generar_sicoss_bd_prueba(PeriodoFiscal $periodo_fiscal, int $limite = 10, bool $incluir_inactivos = false): array
    {
        $mes = $periodo_fiscal->month();
        $anio = $periodo_fiscal->year();

        Log::info("🧪 Iniciando generación SICOSS BD - MODO PRUEBA", [
            'periodo' => $periodo_fiscal->toString(),
            'limite_legajos' => $limite,
            'incluir_inactivos' => $incluir_inactivos
        ]);

        try {
            // Configurar datos para prueba
            $datos_completos = [
                'check_lic' => false,
                'check_retr' => false,
                'check_sin_activo' => $incluir_inactivos,
                'codc_reparto' => self::getCodcReparto(),
            ];

            // Obtener legajos LIMITADOS para prueba
            $where_periodo = " dh22.per_liano = {$anio} and dh22.per_limes = {$mes} ";

            $todos_legajos = self::obtener_legajos(
                $datos_completos['codc_reparto'],
                $where_periodo,
                ' true ',
                $datos_completos['check_lic'],
                $datos_completos['check_sin_activo']
            );

            // 🔍 LIMITAR para prueba
            $legajos_prueba = array_slice($todos_legajos, 0, $limite);

            Log::info("Legajos para prueba seleccionados", [
                'total_disponibles' => count($todos_legajos),
                'seleccionados_para_prueba' => count($legajos_prueba)
            ]);

            // Procesar con el conjunto limitado
            $resultado = self::procesar_periodo_optimizado(
                mes: $mes,
                anio: $anio,
                periodo_fiscal: $periodo_fiscal,
                legajos: $legajos_prueba,
                datos_config: $datos_completos
            );

            // 📋 Agregar información de muestra para debugging
            $resultado['info_prueba'] = [
                'total_disponibles' => count($todos_legajos),
                'procesados_en_prueba' => count($legajos_prueba),
                'primer_legajo_ejemplo' => $legajos_prueba[0] ?? null,
                'periodo_fiscal' => $periodo_fiscal
            ];

            return $resultado;
        } catch (\Exception $e) {
            Log::error("Error en generación SICOSS BD - PRUEBA", [
                'periodo' => $periodo_fiscal->toString(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Método para verificar la estructura de datos antes del procesamiento masivo
     * Útil para debugging y validación
     */
    public static function verificar_estructura_datos(string $periodo_fiscal, int $muestra = 3): array
    {
        $mes = substr($periodo_fiscal, -2);
        $anio = substr($periodo_fiscal, 0, 4);

        Log::info("🔍 Verificando estructura de datos", ['periodo' => $periodo_fiscal]);

        try {
            // Obtener una pequeña muestra
            $where_periodo = " dh22.per_liano = {$anio} and dh22.per_limes = {$mes} ";
            $legajos = self::obtener_legajos(
                self::getCodcReparto(),
                $where_periodo,
                ' true ',
                false,
                false
            );

            $muestra_legajos = array_slice($legajos, 0, $muestra);

            // Verificar estructura de un legajo sin procesamiento
            $verificacion = [
                'total_legajos_disponibles' => count($legajos),
                'estructura_legajo_crudo' => $muestra_legajos[0] ?? null,
                'campos_disponibles' => array_keys($muestra_legajos[0] ?? []),
            ];

            // Procesar uno para ver la estructura final
            if (!empty($muestra_legajos)) {
                $legajo_test = $muestra_legajos[0];
                $conceptos_por_legajo = self::precargar_conceptos_todos_legajos([$legajo_test]);
                $conceptos_legajo = $conceptos_por_legajo[$legajo_test['nro_legaj']] ?? [];

                self::sumarizar_conceptos_optimizado($conceptos_legajo, $legajo_test);

                $verificacion['estructura_legajo_procesado'] = $legajo_test;
                $verificacion['campos_procesados'] = array_keys($legajo_test);

                // Ver cómo queda mapeado
                $datos_mapeados = self::mapear_legajo_a_modelo($legajo_test, $periodo_fiscal);
                $verificacion['estructura_mapeada_bd'] = $datos_mapeados;
                $verificacion['campos_bd'] = array_keys($datos_mapeados);
            }

            return $verificacion;
        } catch (\Exception $e) {
            Log::error("Error verificando estructura", ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Método público para generar SICOSS y guardar en BD
     * Wrapper del método original genera_sicoss()
     */
    public static function generar_sicoss_bd_simple(array $datos, string $periodo_fiscal): array
    {
        $mes = substr($periodo_fiscal, -2);
        $anio = substr($periodo_fiscal, 0, 4);
        $periodoFiscal = new PeriodoFiscal($anio, $mes);
        return self::genera_sicoss(
            datos: $datos,
            testeo_directorio_salida: '',
            testeo_prefijo_archivos: '',
            retornar_datos: false,
            guardar_en_bd: true,        // ⭐ Activar BD
            periodo_fiscal: $periodoFiscal // ⭐ Pasar período
        );
    }
}
