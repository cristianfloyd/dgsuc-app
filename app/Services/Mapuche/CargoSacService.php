<?php

namespace App\Services\Mapuche;

use App\Models\Mapuche\MapucheConfig;
use App\Repositories\Sicoss\Dh03Repository;
use Illuminate\Support\Facades\DB;

class CargoSacService
{
    public $vcl_cargos = [];

    /**
     * Create a new class instance.
     */
    public function __construct()
    {

    }

    public static function getBrutosSacCargo(int $legajo, int $nro_cargo)
    {
        $mes_periodo = (int)MapucheConfig::getMesFiscal();
        $anio_periodo = (int)MapucheConfig::getAnioFiscal();
        if ($mes_periodo <= 6) {
            $segundo_semestre = $anio_periodo - 1;
        } else {
            $segundo_semestre = $anio_periodo;
        }
        $primer_semestre = $anio_periodo;


        $sql = "SELECT
                       dh10.nro_cargo AS clave,
                       dh10.nro_cargo,
                       dh10.vcl_cargo,
                       dh10.imp_bruto_1,
                       dh10.imp_bruto_2,
                       dh10.imp_bruto_3,
                       dh10.imp_bruto_4,
                       dh10.imp_bruto_5,
                       dh10.imp_bruto_6,
                       dh10.imp_bruto_7,
                       dh10.imp_bruto_8,
                       dh10.imp_bruto_9,
                       dh10.imp_bruto_10,
                       dh10.imp_bruto_11,
                       dh10.imp_bruto_12
                   FROM
                       mapuche.dh10,
                       mapuche.dh03
                   WHERE
                       dh10.nro_cargo = dh03.nro_cargo AND
                       dh03.nro_legaj = $legajo
                   ORDER BY
                       nro_cargo desc
                   ";

        $rs = DB::connection(MapucheConfig::getStaticConnectionName())->select($sql);

        foreach ($rs as $registro) {
            $rs1[$registro['clave']] = $registro;
        }

        $rs2 = [];
        $rs2 = $rs1[$nro_cargo];
        $rs2['primer_semestre'] = $primer_semestre;
        $rs2['segundo_semestre'] = $segundo_semestre;
        if (!isset($rs1[$nro_cargo])) {
            for ($i = 1; $i <= 12; $i++) {
                $rs2['imp_bruto_' . $i] = 0;
            }
        }
        for ($i = 1; $i <= 12; $i++) {
            $rs2['imp_acumu_' . $i] = 0;
        }
        for ($i = 1; $i <= 12; $i++) {
            $rs2['vinculo_' . $i] = '';
        }
        $vinculo = $rs1[$nro_cargo]['vcl_cargo'];
        $nro_cargo = $rs1[$nro_cargo]['vcl_cargo'];
        while (isset($rs1[$nro_cargo]) && ($rs1[$nro_cargo]['nro_cargo'] != $rs1[$nro_cargo]['vcl_cargo'])) {
            for ($i = 1; $i <= 12; $i++) {
                $rs2['imp_acumu_' . $i] = $rs2['imp_acumu_' . $i] + $rs1[$nro_cargo]['imp_bruto_' . $i];
                if ($rs1[$nro_cargo]['imp_bruto_' . $i] != 0) {
                    if ($rs2['vinculo_' . $i] == '') {
                        $rs2['vinculo_' . $i] = $vinculo;
                    } else {
                        $rs2['vinculo_' . $i] = $rs2['vinculo_' . $i] . ' -> ' . $vinculo;
                    }
                }
            }
            $vinculo = $rs1[$nro_cargo]['vcl_cargo'];
            $nro_cargo = $rs1[$nro_cargo]['vcl_cargo'];
        }
        if ($rs2['nro_cargo'] != $rs1[$nro_cargo]['nro_cargo']) {
            //Se suman los acumulados del ultimo cargo en los vinculos
            for ($i = 1; $i <= 12; $i++) {
                $rs2['imp_acumu_' . $i] = $rs2['imp_acumu_' . $i] + $rs1[$nro_cargo]['imp_bruto_' . $i];
                if ($rs1[$nro_cargo]['imp_bruto_' . $i] != 0) {
                    if ($rs2['vinculo_' . $i] == '') {
                        $rs2['vinculo_' . $i] = $vinculo;
                    } else {
                        $rs2['vinculo_' . $i] = $rs2['vinculo_' . $i] . ' -> ' . $vinculo;
                    }
                }
            }
        }
        return $rs2;
    }

    public static function modificacion($clave, $datos)
    {
        $clave_sac['nro_cargo'] = $clave;
        for ($i = 1; $i <= 12; $i++) {
            $datos_sac['imp_bruto_' . $i] = $datos['imp_bruto_' . $i];
        }
        DB::connection(MapucheConfig::getStaticConnectionName())->beginTransaction();
        $ok = DB::connection(MapucheConfig::getStaticConnectionName())->table('dh10')->where('nro_cargo', $clave)->update($datos);
        if ($ok) {
            DB::connection(MapucheConfig::getStaticConnectionName())->commit();
            return true;
        }
        DB::connection(MapucheConfig::getStaticConnectionName())->rollBack();
        return false;

    }

    public static function get_brutos_para_sac($filtro, $orderby = '')
    {

        $mes_periodo = (int)MapucheConfig::getMesFiscal();
        $anio_periodo = (int)MapucheConfig::getAnioFiscal();

        if ($orderby != '') {
            $order = ' ORDER BY nyapel ' . $orderby;
        } else {
            $order = 'ORDER BY	dh03.codc_uacad, nyapel, nro_legaj,	fec_baja DESC';
        }

        $where = 'TRUE ';
        if (isset($filtro['cod_regional'])) {
            if ($filtro['cod_regional']['condicion'] == 'es_igual_a') {
                $where .= " AND dh03.codc_regio = '" . $filtro['cod_regional']['valor'] . "'";
            } else {
                $where .= " AND dh03.codc_regio <> '" . $filtro['cod_regional']['valor'] . "'";
            }
        }

        if (isset($filtro['cod_depcia'])) {
            if ($filtro['cod_depcia']['condicion'] == 'es_igual_a') {
                $where .= " AND dh03.codc_uacad = '" . $filtro['cod_depcia']['valor'] . "'";
            } else {
                $where .= " AND dh03.codc_uacad <> '" . $filtro['cod_depcia']['valor'] . "'";
            }
        }

        //Saco el periodo
        if (isset($filtro['periodo'])) {
            //periodo Actual
            if (($filtro['periodo']['condicion'] == 'es_igual_a' && $filtro['periodo']['valor'] == 0) ||
                ($filtro['periodo']['condicion'] == 'es_distinto_de' && $filtro['periodo']['valor'] == 1)
            ) {

                if ($mes_periodo > 6) {
                    $f_alta = $anio_periodo . '-7-1';
                    $fec_baja = $anio_periodo . '-12-31';
                    $p_inicio = 7;
                    $p_fin = 12;
                } else {
                    $f_alta = $anio_periodo . '-1-1';
                    $fec_baja = $anio_periodo . '-6-30';
                    $p_inicio = 1;
                    $p_fin = 6;
                }
            } else { //periodo anterior
                if ($mes_periodo > 6) {
                    $f_alta = $anio_periodo . '-1-1';
                    $fec_baja = $anio_periodo . '-6-30';
                    $p_inicio = 1;
                    $p_fin = 6;
                } else {
                    $anioanterior = $anio_periodo - 1;
                    $f_alta = $anioanterior . '-7-1';
                    $fec_baja = $anioanterior . '-12-31';
                    $p_inicio = 7;
                    $p_fin = 12;
                }
            }
        }

        $filtrowhere = "dh10.nro_cargo = dh03.nro_cargo AND dh01.nro_legaj = dh03.nro_legaj AND
                       (
                           (dh03.fec_alta <= '" . $f_alta . "' AND (dh03.fec_baja >= '" . $fec_baja . "' OR dh03.fec_baja is null)) OR
                           (dh03.fec_alta <= '" . $f_alta . "' AND ((dh03.fec_baja <= '" . $fec_baja . "' AND dh03.fec_baja >= '" . $f_alta . "') OR dh03.fec_baja is null)) OR
                           (dh03.fec_alta >= '" . $f_alta . "' AND dh03.fec_alta <  '" . $fec_baja . "' AND (dh03.fec_baja >= '" . $fec_baja . "' OR dh03.fec_baja is null)) OR
                           (dh03.fec_alta >= '" . $f_alta . "' AND (dh03.fec_baja <= '" . $fec_baja . "' OR dh03.fec_baja is null))
                       ) AND " . $where . ' ';

        $sql = "SELECT
                       dh03.nro_legaj,
                       dh03.fec_baja,
                       dh03.fec_alta,
                       dh03.codc_categ,
                       dh03.codc_uacad,
                       dh01.tipo_docum as tipodoc,
                       dh01.nro_docum as documento,
                       dh01.desc_appat || ', ' ||  dh01.desc_nombr as nyapel,
                       dh10.nro_cargo AS clave,
                       dh10.nro_cargo,
                       dh10.vcl_cargo,
                       dh10.imp_bruto_1,
                       dh10.imp_bruto_2,
                       dh10.imp_bruto_3,
                       dh10.imp_bruto_4,
                       dh10.imp_bruto_5,
                       dh10.imp_bruto_6,
                       dh10.imp_bruto_7,
                       dh10.imp_bruto_8,
                       dh10.imp_bruto_9,
                       dh10.imp_bruto_10,
                       dh10.imp_bruto_11,
                       dh10.imp_bruto_12
                   FROM
                       mapuche.dh10,
                       mapuche.dh03,
                       mapuche.dh01
                   WHERE
                       " . $filtrowhere . ' ' . $order;

        $rs = DB::connection(MapucheConfig::getStaticConnectionName())->select($sql);

        $meses = []; //arreglo con los importes por mes
        $legajo = []; //datos del legajo
        $cargo = []; //datos del cargo vigente
        $cargos = []; //arreglo con TODOS los cargos de los agentes
        $cargosVigentes = [];
        $detalleCargo = []; //detalle del cargo
        $legajos = []; //arreglo en el que se retornan los datos procesados para poder armar la visualizacion de forma mas simple.
        $idAgente = -1; //legajo
        $vinculos = []; //arreglo para ir sumando los imp. para el caso de cargos vinculados

        foreach ($rs as $row) {

            if (\in_array($row['nro_cargo'], $vinculos)) {
                continue;
            }

            if ($idAgente != $row['nro_legaj'] && $idAgente != -1) {

                if (\count($cargosVigentes) == 0) {
                    $cv = [];
                    $cv['nro_cargo'] = '-';
                    $cv['fecha_alta'] = '--/--/----';
                    $cv['fecha_baja'] = '--/--/----';
                    $cv['categoria'] = '';
                    $cv['uacademica'] = '';
                    $cv['vinculado'] = '';
                    $cargosVigentes[] = $cv;
                }

                $cargo['cargos'] = $cargos;
                $cargo['cargos_vigentes'] = $cargosVigentes;
                $legajo['cargo'] = $cargo;
                $legajo['dependencia'] = $row['codc_uacad'];

                $legajos[] = $legajo;
                $cargo = [];
                $cargos = [];
                $legajo = [];
                $idAgente = -1;
                $cargosVigentes = [];

                $meses = [];
            }
            if ($idAgente == $row['nro_legaj'] || $idAgente == -1) { // ==-1 la primera vez q itera

                $idAgente = $row['nro_legaj'];
                if ($row['nro_cargo'] != $row['vcl_cargo']) { //tiene un vinculo, verifico q sea valido
                    $esVinculado = Dh03Repository::esVinculoValido($row['fec_alta'], $row['vcl_cargo']);
                } else {
                    $esVinculado = false;
                }

                if ($esVinculado) {
                    $detalleCargo = [];
                    $continuarVinculos = true;
                    $cargovinculado = $row;
                    while ($continuarVinculos) {

                        if (\count($meses) == 0) {
                            $meses = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];
                        }
                        for ($j = 1; $j <= 12; $j++) {
                            $meses[$j] = $meses[$j] + $cargovinculado['imp_bruto_' . $j];
                        }


                        $dc = [];

                        //detalle del cargo
                        $dc['nro_cargo'] = $cargovinculado['nro_cargo'];
                        $dc['fecha_alta'] = $cargovinculado['fec_alta'];
                        $dc['fecha_baja'] = $cargovinculado['fec_baja'];
                        $dc['categoria'] = $cargovinculado['codc_categ'];

                        $detalleCargo[] = $dc;

                        if ($cargovinculado['nro_cargo'] == $cargovinculado['vcl_cargo']) { //si entra en el if es porque el se termino la cadena de vinculos
                            $continuarVinculos = false;
                        } else { //si tiene vinculo pero el vinculo esta vencido entra por el else
                            $esVinculo = Dh03Repository::esVinculoValido($cargovinculado['fec_alta'], $cargovinculado['vcl_cargo']);
                            if (!$esVinculo) {
                                $continuarVinculos = false;
                            } else {
                                $cargovinculado = self::traerCargo($cargovinculado['vcl_cargo'], $filtrowhere);
                            }
                            if ($cargovinculado == false) {
                                $continuarVinculos = false;
                            } else {
                                $vinculos[] = $cargovinculado['nro_cargo'];
                            }
                        }
                    } //fin del while

                    if ($row['fec_baja'] == '' || (strtotime($row['fec_baja']) >= strtotime($anio_periodo . '-' . $mes_periodo . '-' . self::get_dias_mes($mes_periodo, $anio_periodo)))) {
                        $cv = [];
                        $cv['nro_cargo'] = $row['nro_cargo'];
                        $cv['fecha_alta'] = $row['fec_alta'];
                        if ($row['fec_baja'] != '') {
                            $cv['fecha_baja'] = $row['fec_baja'];
                        } else {
                            $cv['fecha_baja'] = '--/--/----';
                        }
                        $cv['categoria'] = $row['codc_categ'];
                        $cv['uacademica'] = $row['codc_uacad'];
                        $cv['vinculado'] = 'VINC';
                        $cargosVigentes[] = $cv;
                    }
                    if (\count($legajo) == 0) {
                        $legajo['nro_legaj'] = $row['nro_legaj'];
                        $legajo['nyapel'] = $row['nyapel'];
                        $legajo['documento'] = $row['documento'];
                        $legajo['tipodoc'] = $row['tipodoc'];
                        $legajo['dependencia'] = $row['codc_uacad'];
                    }


                    //Arreglo de meses con los importes
                    $mayor = ['pos' => 0, 'imp' => 0];
                    for ($j = $p_inicio; $j <= $p_fin; $j++) {
                        if ($meses[$j] > $mayor['imp']) {
                            $mayor['pos'] = $j;
                            $mayor['imp'] = $meses[$j];
                        }
                    }
                    $meses['mayor'] = $mayor;


                    $c = [];
                    $c['meses'] = $meses;
                    $c['detalle_cargo'] = $detalleCargo;
                    $cargos[] = $c;
                    $meses = [];
                }


                //Esta condicion es para ver si el cargo no tiene ningun vinculo
                if ($row['nro_cargo'] == $row['vcl_cargo'] || !$esVinculado) {
                    $detalleCargo = [];
                    $meses = [];

                    if (\count($legajo) == 0) {
                        $legajo['nro_legaj'] = $row['nro_legaj'];
                        $legajo['nyapel'] = $row['nyapel'];
                        $legajo['documento'] = $row['documento'];
                        $legajo['tipodoc'] = $row['tipodoc'];
                        $legajo['dependencia'] = $row['codc_uacad'];
                    }

                    //Arreglo de meses con los importes
                    $mayor = ['pos' => 0, 'imp' => 0];
                    for ($j = $p_inicio; $j <= $p_fin; $j++) {
                        if ($row['imp_bruto_' . $j] > $mayor['imp']) {
                            $mayor['pos'] = $j;
                            $mayor['imp'] = $row['imp_bruto_' . $j];
                        }
                        $meses[$j] = $row['imp_bruto_' . $j];
                    }
                    $meses['mayor'] = $mayor;

                    //detalle del cargo
                    $dc = [];
                    $dc['nro_cargo'] = $row['nro_cargo'];
                    $dc['fecha_alta'] = $row['fec_alta'];
                    $dc['fecha_baja'] = $row['fec_baja'];
                    $dc['categoria'] = $row['codc_categ'];

                    $detalleCargo[] = $dc;

                    if ($row['fec_baja'] == '' || (strtotime($row['fec_baja']) >= strtotime($anio_periodo . '-' . $mes_periodo . '-' . self::get_dias_mes($mes_periodo, $anio_periodo)))) {
                        $cv = [];
                        $cv['nro_cargo'] = $row['nro_cargo'];
                        $cv['fecha_alta'] = $row['fec_alta'];
                        if ($row['fec_baja'] != '') {
                            $cv['fecha_baja'] = $row['fec_baja'];
                        } else {
                            $cv['fecha_baja'] = '--/--/----';
                        }
                        $cv['categoria'] = $row['codc_categ'];
                        $cv['uacademica'] = $row['codc_uacad'];
                        $cv['vinculado'] = '';
                        $cargosVigentes[] = $cv;
                    }

                    $c = [];
                    $c['meses'] = $meses;
                    $c['detalle_cargo'] = $detalleCargo;
                    $cargos[] = $c;
                    $meses = [];
                }
            }
        }
        if (\count($rs) > 0) {
            //Es para el ultimo caso
            if (\count($cargosVigentes) == 0) {
                $cv = [];
                $cv['nro_cargo'] = '-';
                $cv['fecha_alta'] = '--/--/----';
                $cv['fecha_baja'] = '--/--/----';
                $cv['categoria'] = '';
                $cv['uacademica'] = '';
                $cv['vinculado'] = '';
                $cargosVigentes[] = $cv;
            }
            $cargo['cargos'] = $cargos;

            $cargo['cargos_vigentes'] = $cargosVigentes;
            $legajo['cargo'] = $cargo;
            $legajos[] = $legajo;
        }

        return $legajos;

        /* Estructura de retorno
            *  Legajos Array(
            *  				[nro_legaj] => INT
            *  				[nyapel]    => VARCHAR
            *  				[cargo]		=> Array(
            *  										[cargos]=> Array (
            *  															Array (
            *  																	[meses] => Array(Arreglo de meses)
            *  																	[detalle_cargo] => Array(
            *  																							Array
            *													                                                (
            *													                                                    [nro_cargo] => INT
            *													                                                    [fecha_alta] => DATE
            *													                                                    [fecha_baja] => DATE
            *													                                                    [categoria] => VARCHAR
            *													                                                )
            *  																							)
            *  														 		   )
            *  														  )
            *  										[cargos_vigentes] => Array(
            *  																	Array
            *											                                (
            *											                                    [nro_cargo] => INT
            *											                                    [fecha_alta] => DATE
            *											                                    [fecha_baja] => DATE
            *																				[categoria] => VARCHAR
            *																				[vinculado] => VARCHAR
            *																				[uacademica] => VARCHAR
            *											                                )
            *  																  )
            *  									)
            *
            * */
    }

    public static function traerCargo($nroCargo, $filtro = ' true ')
    {

        $sql = "SELECT
                       dh03.nro_legaj,
                       dh03.fec_baja,
                       dh03.fec_alta,
                       dh03.codc_categ,
                       dh01.desc_appat || ', ' ||  dh01.desc_nombr as nyapel,
                       dh10.nro_cargo AS clave,
                       dh10.nro_cargo,
                       dh10.vcl_cargo,
                       dh10.imp_bruto_1,
                       dh10.imp_bruto_2,
                       dh10.imp_bruto_3,
                       dh10.imp_bruto_4,
                       dh10.imp_bruto_5,
                       dh10.imp_bruto_6,
                       dh10.imp_bruto_7,
                       dh10.imp_bruto_8,
                       dh10.imp_bruto_9,
                       dh10.imp_bruto_10,
                       dh10.imp_bruto_11,
                       dh10.imp_bruto_12
                   FROM
                       mapuche.dh10,
                       mapuche.dh03,
                       mapuche.dh01
                   WHERE
                       " . $filtro . ' AND
                       dh03.nro_cargo =' . $nroCargo . '

                   ORDER BY
                       nro_legaj DESC,
                       fec_baja DESC
                   ';

        $result = DB::connection(MapucheConfig::getStaticConnectionName())->select($sql);
        if (\count($result) > 0) {
            return $result[0];
        }

        return false;
    }

    public static function getCargoBasicos(int $nro_cargo, bool $alta = false)
    {
        $datos_cargo_sac = [];
        if (isset($nro_cargo)) {
            $sql = "SELECT
                                dh10.nro_cargo,
                               dh10.vcl_cargo,
                               dh10.imp_bruto_1,
                               dh10.imp_bruto_2,
                               dh10.imp_bruto_3,
                               dh10.imp_bruto_4,
                               dh10.imp_bruto_5,
                               dh10.imp_bruto_6,
                               dh10.imp_bruto_7,
                               dh10.imp_bruto_8,
                               dh10.imp_bruto_9,
                               dh10.imp_bruto_10,
                               dh10.imp_bruto_11,
                               dh10.imp_bruto_12
                           FROM
                               mapuche.dh10
                           WHERE
                               dh10.nro_cargo = $nro_cargo
                           ";
            $datos_cargo_sac = DB::connection(MapucheConfig::getStaticConnectionName())->selectOne($sql);
            if (!\is_array($datos_cargo_sac)) {
                $datos_cargo_sac = [];
            } // retorna false si no obtiene datos y despues se lo trata como array
            // Si no existe o es alta preparo la nueva entrada para dh10, necesito igualar todos los valores en cero sino se guarda como nulls
            if (empty($datos_cargo_sac) || $alta) {
                for ($i = 1; $i <= 12; $i++) {
                    $datos_cargo_sac['imp_bruto_' . $i] = 0;
                    $datos_cargo_sac['importes_retro_' . $i] = 0;
                }

                for ($i = 1; $i <= 18; $i++) {
                    $datos_cargo_sac['retroimpbrhbrpr_' . $i] = 0;
                    $datos_cargo_sac['impbrhbrprom_' . $i] = 0;
                }

                $datos_cargo_sac['vcl_cargo'] = $nro_cargo;
                $datos_cargo_sac['nro_cargo'] = $nro_cargo;
            }
            return $datos_cargo_sac;
        }
    }

    public static function get_dias_sac_periodo($fin, $inicio, $dias)
    {
        $arreglo = [];
        for ($i = $inicio; $i <= $fin; $i++) {
            for ($j = 1; $j <= $dias; $j++) {
                $arreglo[$i][$j] = false;
            }
        }
        return $arreglo;
    }

    /**
     * Obtiene el número de días de un mes específico.
     *
     * @param int $mes Mes (1-12)
     * @param int $anio Año (formato YYYY)
     *
     * @throws \InvalidArgumentException Si el mes o año son inválidos
     *
     * @return int Número de días del mes
     */
    public static function get_dias_mes(int $mes, int $anio): int
    {
        // Validación de parámetros
        if ($mes < 1 || $mes > 12) {
            throw new \InvalidArgumentException("El mes debe estar entre 1 y 12. Recibido: {$mes}");
        }

        if ($anio < 1900 || $anio > 2100) {
            throw new \InvalidArgumentException("El año debe estar entre 1900 y 2100. Recibido: {$anio}");
        }

        try {
            // Usar Carbon que es más robusto y es el estándar en Laravel
            return \Carbon\Carbon::createFromDate($anio, $mes, 1)->daysInMonth;
        } catch (\Carbon\Exceptions\InvalidFormatException $e) {
            throw new \InvalidArgumentException("Fecha inválida: mes {$mes}, año {$anio}", 0, $e);
        }
    }

    /**
     * Obtiene el número de días de un mes específico con cache
     * Versión optimizada para consultas frecuentes.
     *
     * @param int $mes Mes (1-12)
     * @param int $anio Año (formato YYYY)
     *
     * @return int Número de días del mes
     */
    public static function get_dias_mes_cached(int $mes, int $anio): int
    {
        $cacheKey = "dias_mes_{$anio}_{$mes}";

        return \Illuminate\Support\Facades\Cache::remember($cacheKey, 3600, function () use ($mes, $anio) {
            return self::get_dias_mes($mes, $anio);
        });
    }

    /**
     * Obtiene información completa del mes.
     *
     * @param int $mes Mes (1-12)
     * @param int $anio Año (formato YYYY)
     *
     * @return array Información del mes
     */
    public static function get_info_mes(int $mes, int $anio): array
    {
        $fecha = \Carbon\Carbon::createFromDate($anio, $mes, 1);

        return [
            'dias' => $fecha->daysInMonth,
            'nombre' => $fecha->translatedFormat('F'),
            'nombre_corto' => $fecha->translatedFormat('M'),
            'primer_dia' => $fecha->startOfMonth()->translatedFormat('l'),
            'ultimo_dia' => $fecha->endOfMonth()->translatedFormat('l'),
            'es_bisiesto' => $fecha->isLeapYear(),
            'trimestre' => $fecha->quarter,
        ];
    }

    // esta funcion, se utilizaria de la misma manera en laravel?
}
