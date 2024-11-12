<?php

namespace App\Mapuche\Gerencial;

use toba;
use toba_error_db;
use toba_fecha;

define("MAP_FECHA_MINIMA", '1900-1-1');
define("MAP_FECHA_MAXIMA", '2999-12-31');

class Fechas
{

    /**
     * @throws toba_error_db
     */
    static function es_igual($fecha1, $fecha2)
    {
        $fecha1 = empty($fecha1) ? MAP_FECHA_MINIMA : $fecha1;
        $fecha2 = empty($fecha2) ? MAP_FECHA_MINIMA : $fecha2;
        $fecha1 = quote($fecha1);
        $fecha2 = quote($fecha2);

        $sql = "SELECT (DATE $fecha1 = DATE $fecha2) as es_igual";

        $rs = toba::db()->consultar_fila($sql);
        return $rs['es_igual'];
    }

    /**
     * @throws toba_error_db
     */
    static function es_menor_igual($fecha1, $fecha2)
    {
        $fecha1 = empty($fecha1) ? MAP_FECHA_MINIMA : $fecha1;
        $fecha2 = empty($fecha2) ? MAP_FECHA_MAXIMA : $fecha2;
        $fecha1 = quote($fecha1);
        $fecha2 = quote($fecha2);

        $sql = "SELECT (DATE $fecha1 <= DATE $fecha2) as es_menor_igual";

        $rs = toba::db()->consultar_fila($sql);
        return $rs['es_menor_igual'];
    }

    /**
     * @throws toba_error_db
     */
    static function es_menor($fecha1, $fecha2)
    {
        $fecha1 = empty($fecha1) ? MAP_FECHA_MINIMA : $fecha1;
        $fecha2 = empty($fecha2) ? MAP_FECHA_MINIMA : $fecha2;
        $fecha1 = quote($fecha1);
        $fecha2 = quote($fecha2);

        $sql = "SELECT (DATE $fecha1 < DATE $fecha2) as es_menor";
        $rs = toba::db()->consultar_fila($sql);
        return $rs['es_menor'];
    }

    /**
     * @throws toba_error_db
     */
    static function es_mayor_igual($fecha1, $fecha2)
    {
        $fecha1 = empty($fecha1) ? MAP_FECHA_MAXIMA : $fecha1;
        $fecha2 = empty($fecha2) ? MAP_FECHA_MAXIMA : $fecha2;
        $fecha1 = quote($fecha1);
        $fecha2 = quote($fecha2);

        $sql = "SELECT (DATE $fecha1 >= DATE $fecha2) as es_mayor_igual";
        try {
            $rs = toba::db()->consultar_fila($sql);
        } catch (toba_error_db $e) {
            toba::logger()->debug($sql);
            throw $e;
        }
        return $rs['es_mayor_igual'];
    }

    static function es_mayor_igual_php($fecha1, $fecha2)
    {
        $fecha1 = empty($fecha1) ? MAP_FECHA_MAXIMA : $fecha1;
        $fecha2 = empty($fecha2) ? MAP_FECHA_MAXIMA : $fecha2;
        $fecha1 = new DateTime($fecha1);
        $fecha2 = new DateTime($fecha2);
        return ($fecha1 >= $fecha2) ? true : false;
    }

    /**
     * @throws toba_error_db
     */
    static function es_mayor($fecha1, $fecha2)
    {
        $fecha1 = empty($fecha1) ? MAP_FECHA_MAXIMA : $fecha1;
        $fecha2 = empty($fecha2) ? MAP_FECHA_MAXIMA : $fecha2;
        $fecha1 = quote($fecha1);
        $fecha2 = quote($fecha2);

        $sql = "SELECT (DATE $fecha1 > DATE $fecha2) as es_mayor";
        $rs = toba::db()->consultar_fila($sql);
        return $rs['es_mayor'];
    }

    /**
     * Dada una fecha en formato yyyy-mm-dd retorna un string del tipo 'Dia Mes de A�o'
     */
    static function get_desc_fecha($fecha_base)
    {
        if (isset($fecha_base)) {
            $fecha = explode('-', $fecha_base);
            $toba_fecha = new toba_fecha();
            $meses = $toba_fecha->get_meses_anio();
            $mes = $meses[(integer)$fecha[1] - 1]['mes'];
            return (integer)$fecha[2] . ' de ' . $mes . ' de ' . $fecha[0];
        }
    }

    static function get_dias_mes_30($mes, $anio)
    {
        $dias = fechas::get_dias_mes($mes, $anio);
        if ($dias > 30)
            $dias = 30;
        return $dias;
    }

    static function get_dias_mes($mes, $anio)
    {
        if (is_callable('cal_days_in_month')) {
            return cal_days_in_month(CAL_GREGORIAN, $mes, $anio);
        } else {
            $fecha = new DateTime($anio . '-' . $mes . '-01');
            return date_format($fecha, 't');
        }
    }

    static function get_dia_fecha_juliana($fecha_juliana)
    {
        if (isset($fecha_juliana)) {
            if (str_contains($fecha_juliana, '-') || str_contains($fecha_juliana, '/')) {
                //esta en gregoriano
                return fechas::get_fecha_gregoriano($fecha_juliana, 'dia');
            } else {
                //la convierto a gregoriano
                $fecha_gregoriano = fechas::juliana_to_gregoriano($fecha_juliana);
                $fecha = explode('/', $fecha_gregoriano);
                return $fecha[1];
            }

        }
    }

    private static function get_fecha_gregoriano($fecha, $parte = null): string
    {
        $fecha_formateada = date_format(date_create($fecha), 'm-d-Y');
        $fecha = explode('-', $fecha_formateada);

        switch ($parte) {
            case 'dia':
                return $fecha[1];
                break;

            case 'mes':
                return $fecha[0];
                break;

            case 'anio':
                return $fecha[2];
                break;

            default:
                return $fecha_formateada;
                break;
        }
    }

    static function juliana_to_gregoriano($juliana): string
    {
        return JDToGregorian($juliana);
    }

    static function get_mes_fecha_juliana($fecha_juliana)
    {
        if (isset($fecha_juliana)) {
            if (str_contains($fecha_juliana, '-') || str_contains($fecha_juliana, '/')) {
                // está en gregoriano
                return fechas::get_fecha_gregoriano($fecha_juliana, 'mes');
            } else {
                //la convierto a gregoriano
                $fecha_gregoriano = fechas::juliana_to_gregoriano($fecha_juliana);
                $fecha = explode('/', $fecha_gregoriano);
                return $fecha[0];
            }
        }
    }

    static function get_anio_fecha_juliana($fecha_juliana)
    {
        if (isset($fecha_juliana)) {
            if (str_contains($fecha_juliana, '-') || str_contains($fecha_juliana, '/')) {
                //esta en gregoriano
                return fechas::get_fecha_gregoriano($fecha_juliana, 'anio');
            } else {
                //la convierto a gregoriano
                $fecha_gregoriano = fechas::juliana_to_gregoriano($fecha_juliana);
                $fecha = explode('/', $fecha_gregoriano);
                return $fecha[2];
            }
        }
    }

    /**
     * Obtiene la hora del servidor de base de datos
     * Informa hora, min y segundos en 3 campos de un arreglo
     * @throws toba_error_db
     */
    static function get_hora_db(): array
    {
        $sql = "SELECT EXTRACT(HOUR FROM CURRENT_TIME) AS hora, EXTRACT(MINUTE FROM CURRENT_TIME) AS min, EXTRACT(SECOND FROM CURRENT_TIME) AS seg";
        return toba::db()->consultar_fila($sql);
    }

    /**
     * Obtiene una nueva fecha, a partir de $fecha desplazada en $cant,
     * ya sean d�as, meses o a�os.
     * Por defecto, d�as.
     * @throws toba_error_db
     */
    static function get_fecha_desplazada($fecha, $cant, $parte = 'dia')
    {
        switch ($parte) {
            case 'dia' :
                $parte = 'day';
                break;
            case 'mes':
                $parte = 'month';
                break;
            case 'anio':
                $parte = 'year';
                break;
        }

        $sql = "SELECT ('$fecha'::date + interval '$cant $parte')::date as fecha;";
        toba::logger()->debug($sql);
        $rs = toba::db()->consultar_fila($sql);
        return $rs['fecha'];
    }

    /**
     * Obtiene la cantidad de meses entre dos fechas
     */
    static function get_cantidad_meses_entre($fechaFinal, $fechaInicial): float|int|string|null
    {
        if ($fechaFinal[0] == "'") $fechaFinal = substr($fechaFinal, 1, -1);
        if ($fechaInicial[0] == "'") $fechaInicial = substr($fechaInicial, 1, -1);
        return ((fechas::get_anio_fecha($fechaFinal) - fechas::get_anio_fecha($fechaInicial)) * 12 +
            fechas::get_mes_fecha($fechaFinal) - fechas::get_mes_fecha($fechaInicial));
    }

    static function get_anio_fecha($fecha_base)
    {
        if (isset($fecha_base)) {
            $fecha = explode('-', $fecha_base);
            return $fecha[0];
        }
    }

    static function get_mes_fecha($fecha_base)
    {
        if (isset($fecha_base)) {
            $fecha = explode('-', $fecha_base);
            return $fecha[1];
        }
    }

    /**
     * Obtiene la cantidad de días entre dos fechas
     * @throws toba_error_db
     */
    static function get_dias_entre($fecha1, $fecha2)
    {
        $fecha1 = quote($fecha1);
        $fecha2 = quote($fecha2);

        $sql = "SELECT ABS(DATE $fecha1 - DATE $fecha2) as dias";
        toba::logger()->debug($sql);
        $rs = toba::db()->consultar_fila($sql);
        return $rs['dias'];
    }

    /**
     * Controla que alguno de los días de inicio estén incluidos en el rango de fechas fin.
     */

    static function fechas_solapadas($f_inicio1, $f_final1, $f_inicio2, $f_final2): bool
    {
        if (self::rango_incluido_en_rango($f_inicio1, $f_final1, $f_inicio2, $f_final2, 'OR'))
            return true;
        if (self::rango_incluido_en_rango($f_inicio2, $f_final2, $f_inicio1, $f_final1, 'OR'))
            return true;
        if (self::rango_incluido_en_rango($f_inicio1, $f_final1, $f_inicio2, $f_inicio2, 'OR'))
            return true;
        if (self::rango_incluido_en_rango($f_inicio1, $f_final1, $f_final2, $f_final2, 'OR'))
            return true;
        return false;
    }

    /**
     * Determina si un rango de fechas esta incluido en otro (si los primeros dos valores estan incluidos en el segundo par de valores).
     * Si alguna de las fechas es NULL, las acomoda a las fechas minima y maxima.
     */
    static function rango_incluido_en_rango($f_inicio1, $f_final1, $f_inicio2, $f_final2, $operador = 'AND')
    {

        $f_inicio1 = empty($f_inicio1) ? MAP_FECHA_MINIMA : $f_inicio1;
        $f_final1 = empty($f_final1) ? MAP_FECHA_MAXIMA : $f_final1;
        $f_inicio2 = empty($f_inicio2) ? MAP_FECHA_MINIMA : $f_inicio2;
        $f_final2 = empty($f_final2) ? MAP_FECHA_MAXIMA : $f_final2;

        $f_inicio1 = quote($f_inicio1);
        $f_final1 = quote($f_final1);
        $f_inicio2 = quote($f_inicio2);
        $f_final2 = quote($f_final2);

        $sql = "SELECT
		            ($f_inicio1 BETWEEN DATE $f_inicio2 AND DATE $f_final2)
		            $operador
		            ($f_final1 BETWEEN DATE $f_inicio2 AND DATE $f_final2) as incluye_rango
		          ";

        $rs = toba::db()->consultar_fila($sql);
        toba::logger()->debug($sql);
        return $rs['incluye_rango'];
    }

    /**
     * Devuelve verdadero o falso según si en la fecha ingresada es un día entre lunes y viernes.
     * @throws toba_error_db
     */
    static function es_dia_de_semana($fecha): bool
    {
        if (isset($fecha)) {
            $fecha = quote($fecha);

            $sql = "SELECT EXTRACT(DOW FROM DATE $fecha) as dia_semana";
            $rs = toba::db()->consultar_fila($sql);
            return (($rs['dia_semana'] > 0) && ($rs['dia_semana'] < 6));
        }
        return true;
    }

    static function es_domingo($fecha): bool
    {
        return self::get_dia_semana($fecha) == 0;
    }

    /**
     * @throws toba_error_db
     */
    static function get_dia_semana($fecha)
    {
        if (isset($fecha)) {
            $fecha = quote($fecha);
            $sql = "SELECT EXTRACT(DOW FROM DATE $fecha) as dia_semana";
            $rs = toba::db()->consultar_fila($sql);
            return $rs['dia_semana'];
        }
    }

    /**
     * @throws toba_error_db
     */
    static function es_sabado($fecha): bool
    {
        return self::get_dia_semana($fecha) == 6;
    }

    static function es_feriado($fecha, $escalafon): bool
    {
        $fecha = quote($fecha);
        $where = "tipo_escalafon = 'T'";
        if (isset($escalafon)) {
            $escalafon = quote($escalafon);
            $where .= " OR tipo_escalafon = $escalafon";
        }

        $sql = "
				SELECT
					count(*) as cant_feriados
				FROM
					" . MAP_ESQUEMA . ".dl06 dl06
				WHERE
					fech_feriado = $fecha
				AND	($where)
			";
        $rs = mapuche::consultar_fila($sql);
        return $rs['cant_feriados'] > 0;
    }

    static function get_cantidad_dias_habiles($fec_desde, $fec_hasta, $escalafon, $usa_tabla_feriados = false, $trabaja_sabado = false, $trabaja_domingo = false)
    {

        $where = 'TRUE';
        $where_feriados = 'TRUE';
        $fec_desde = quote($fec_desde);
        $fec_hasta = quote($fec_hasta);
        $escalafon = quote($escalafon);

        //-- Control si trabaja Sabado y/o Domingo
        $dias = array();
        if (!$trabaja_domingo) {
            $dias[] = 0;
        }
        if (!$trabaja_sabado) {
            $dias[] = 6;
        }
        if (!empty($dias)) {
            $dias = implode(',', $dias);
            $where = "NOT date_part('dow', $fec_desde::date + i) IN ($dias)";
        }
        if ($escalafon != 'T') {
            $where_feriados = "(tipo_escalafon = $escalafon OR tipo_escalafon = 'T')";
        }
        //-- Usa la tabla de feriados para el control?
        if ($usa_tabla_feriados) {
            $where .= " AND NOT $fec_desde::date + i IN (
											SELECT
												fech_feriado
											FROM
												" . MAP_ESQUEMA . ".dl06 dl06
											WHERE
												$where_feriados
												AND fech_feriado = $fec_desde::date + i
											)
						";
        }
        $sql = "SELECT
					COUNT(*) as cant_dias_habiles
				FROM
					generate_series(0, ($fec_hasta::date - $fec_desde::date)) i
				WHERE
					$where
				";
        toba::logger()->debug($sql);
        $rs = toba::db()->consultar_fila($sql);

        return $rs['cant_dias_habiles'];
    }

    static function dia_entre_fechas($fecha_entre, $fecha_inicio, $fecha_fin)
    {
        $fecha_entre = quote($fecha_entre);
        $fecha_inicio = quote($fecha_inicio);
        $fecha_fin = quote($fecha_fin);

        $sql = "SELECT
		            ($fecha_entre BETWEEN DATE $fecha_inicio AND DATE $fecha_fin) as fecha_entre;
		          ";

        $rs = toba::db()->consultar_fila($sql);
        return $rs['fecha_entre'];
    }

    static function gregoriano_to_juliana($fecha)
    {
        // Parseo la fecha para la conversion
        $arrFecha = date_parse($fecha);

        // Convierte la fecha a formato juliana (dias)
        return gregoriantojd($arrFecha["month"], $arrFecha["day"], $arrFecha["year"]);
    }

    static function get_fecha_fin_periodo_corriente()
    {
        $sql = "SELECT map_get_fecha_fin_periodo();";
        $rs = toba::db()->consultar_fila($sql);
        return $rs['map_get_fecha_fin_periodo'];
    }

    static function get_fecha_inicio_periodo_corriente()
    {
        $sql = "SELECT map_get_fecha_inicio_periodo();";
        $rs = toba::db()->consultar_fila($sql);
        return $rs['map_get_fecha_inicio_periodo'];
    }

    // Retorna fecha de fin del periodo vigente

    /**
     * Retorna el d�a de hoy dentro del per�odo corriente
     */
    static function get_fecha_hoy_periodo_corriente()
    {
        $periodo_corriente = mapuche::get_periodo_corriente();
        $fecha = self::armar_fecha(self::get_dia_fecha(self::get_fecha_db()), $periodo_corriente['per_mesct'], $periodo_corriente['per_anoct']);
        return $fecha;
    }

    // Retorna fecha de inicio del periodo vigente

    /**
     * Dado dia, mes, a�o. Retorna la fecha concatenada con "-" Ej: 2006-04-28
     */
    static function armar_fecha($dia, $mes, $anio)
    {
        return $anio . "-" . str_pad($mes, 2, "0", STR_PAD_LEFT) . "-" . str_pad($dia, 2, "0", STR_PAD_LEFT);
    }

    static function get_dia_fecha($fecha_base)
    {
        if (isset($fecha_base)) {
            $fecha = explode('-', $fecha_base);
            return $fecha[2];
        }
    }

    /**
     * Por defecto devuelve la fecha de la base.
     * Opcionalmente se le puede pedir que parte de la fecha se necesita.
     */
    static function get_fecha_db($parte = null)
    {
        $sql = "SELECT CURRENT_DATE as fecha_actual";
        $rs = toba::db()->consultar_fila($sql);
        switch ($parte) {
            case 'dia':
                $resultado = self::get_dia_fecha($rs['fecha_actual']);
                break;
            case 'mes':
                $resultado = self::get_mes_fecha($rs['fecha_actual']);
                break;
            case 'a�o':
                $resultado = self::get_anio_fecha($rs['fecha_actual']);
                break;
            default:
                $resultado = $rs['fecha_actual'];
        }
        return $resultado;
    }

    /**
     * Retorna la fecha fin (con dia) del mes y a�o pasado. Ej: 2008-10-31
     */
    static function get_fecha_fin($mes, $anio)
    {
        $fecha = new DateTime($anio . '-' . $mes . '-01');
        $dia_ult_corriente = date_format($fecha, 't');
        return $anio . "-" . $mes . "-" . $dia_ult_corriente;
    }

    static function get_dia_fin($mes, $anio)
    {
        return date("t", strtotime("$anio-$mes-01"));
    }

    /**
     * Dado dia, mes, a�o. Retorna true si es una fecha valida, sino retorna false
     */
    static function es_fecha($dia, $mes, $anio)
    {
        if (is_numeric($dia) and is_numeric($mes) and is_numeric($anio)) {
            if (checkdate($mes, $dia, $anio))
                return true;
            else
                return false;
        } else
            return false;
    }

    /**
     * Dados dos periodos (mes y anio) retorna Verdadero si el primero es mayor que el segundo
     */
    static function es_mayor_periodo($mes1, $anio1, $mes2, $anio2)
    {
        if ($anio1 > $anio2 or ($anio1 == $anio2 and $mes1 > $mes2)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Formatea un timestamp o un tipo date (si $date es true)
     *
     * @param string &$timestamp
     * @param boolean $date
     */
    static public function formatear_timestamp(&$timestamp, $date = false)
    {
        $time_array = date_parse($timestamp);
        $time_array['hour'] = ($time_array['hour'] > 9) ? $time_array['hour'] : '0' . $time_array['hour'];
        $time_array['minute'] = ($time_array['minute'] > 9) ? $time_array['minute'] : '0' . $time_array['minute'];
        $time_array['day'] = ($time_array['day'] > 9) ? $time_array['day'] : '0' . $time_array['day'];
        $time_array['month'] = ($time_array['month'] > 9) ? $time_array['month'] : '0' . $time_array['month'];
        $timestamp = $time_array['day'] . '/' . $time_array['month'] . '/' . $time_array['year'];
        if (!$date) {
            $timestamp .= ' ' . $time_array['hour'] . ':' . $time_array['minute'];
        }
    }

}

