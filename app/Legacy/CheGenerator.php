<?php

namespace App\Legacy;

use Illuminate\Support\Facades\DB;
use App\Models\Mapuche\MapucheConfig;
use App\Traits\MapucheConnectionTrait;

class CheGenerator
{
    use MapucheConnectionTrait;
    protected $mensaje = null;
    protected $diferencias = [];
    protected $totales = null;
    protected $archivos;
    protected $liquidaciones = [];
    protected $liqui_actual;
    protected $log;
    protected $porc_parcial;
    protected $netos;
    protected $campo = null;

    /**
     * Rellena valores numéricos con ceros a la izquierda
     */
    public function llenaImportes($valor, $longitud): string
    {
        if (strlen(trim($valor)) > $longitud) {
            return substr($valor, - ($longitud));
        }
        return str_pad($valor, $longitud, "0", STR_PAD_LEFT);
    }

    /**
     * Rellena texto con espacios a la derecha
     */
    public function llenaBlancos($texto, $longitud): string
    {
        if (strlen(trim($texto)) > $longitud) {
            return substr($texto, - ($longitud));
        }
        return str_pad($texto, $longitud, " ", STR_PAD_RIGHT);
    }

    /**
     * Rellena texto con espacios a la izquierda
     */
    public function llenaBlancoIzq($texto, $longitud): string
    {
        if (strlen(trim($texto)) > $longitud) {
            return substr($texto, - ($longitud));
        }
        return str_pad($texto, $longitud, " ", STR_PAD_LEFT);
    }

    /**
     * Obtiene los datos base para el archivo CHE
     */
    public function getDatos($liquidacion): void
    {
        // Limpieza inicial de tablas temporales
        DB::connection($this->getConnectionName())->statement("DROP TABLE if exists l");
        DB::connection($this->getConnectionName())->statement("DROP TABLE if exists l2");
        DB::connection($this->getConnectionName())->statement("DROP TABLE if exists d21tmp");
        DB::connection($this->getConnectionName())->statement("DROP TABLE if exists che");

        // Primera tabla temporal 'l' con datos base
        if (MapucheConfig::getParametrosAjustesImpContable() == 'Deshabilitada') {
            $this->createBasicTempTable($liquidacion);
        } else {
            $this->createExtendedTempTable($liquidacion);
        }

        // Creación de tabla temporal d21tmp con datos de cargos
        $this->createD21TempTable();

        // Actualización de escalafones
        DB::connection($this->getConnectionName())->statement(
            "UPDATE d21tmp SET tipoescalafon = 'S' WHERE tipoescalafon = 'C';"
        );

        // Creación de segunda tabla temporal l2
        $this->createL2TempTable();

        // Inserción de registros no duplicados
        DB::connection($this->getConnectionName())->statement(
            "INSERT INTO l SELECT * FROM l2 WHERE id_liquidacion NOT IN (SELECT id_liquidacion FROM l);"
        );

        // Creación de tabla temporal che para aportes
        $this->createCheTable();

        // Actualización de descripciones de grupo
        $this->updateCheGroupDescriptions();

        // Inserción de datos en la tabla de la base de datos
        $this->insertIntoDatabase();
    }

    public function createBasicTempTable($liquidacion): void
    {
        $sql = "SELECT
                        dh21.*,
                        dh22.codn_econo,
                        dh22.nrovalorpago
                    INTO TEMP l
                    FROM
                        mapuche.dh21,
                        mapuche.dh22
                    WHERE
                        dh22.nro_liqui = dh21.nro_liqui AND
                        dh21.nro_liqui = ? AND
                        dh21.nro_orimp > 0 AND
                        dh21.codn_conce > 0
                    ORDER BY
                        dh21.codc_uacad";

        DB::connection($this->getConnectionName())->statement($sql, [$liquidacion]);
    }

    public function createExtendedTempTable($liquidacion): void
    {
        $sql = "SELECT
                        imp_liq.*,
                        dh22.codn_econo,
                        dh22.nrovalorpago,
                        imp_par.ejercicio as tipo_ejercicio,
                        imp_par.grupo_presupuestario as codn_grupo_presup,
                        imp_par.unidad_principal as codn_area,
                        imp_par.sub_unidad as codn_subar,
                        imp_par.sub_sub_unidad as codn_subsubar,
                        imp_par.fuente_financiamiento as codn_fuent,
                        imp_par.programa as codn_progr,
                        imp_par.sub_programa as codn_subpr,
                        imp_par.proyecto as codn_proye,
                        imp_par.actividad as codn_activ,
                        imp_par.obra as codn_obra,
                        imp_par.inciso as inciso,
                        imp_par.partida_principal as partida_principal,
                        imp_par.partida_parcial as partida_parcial,
                        imp_par.partida_subparcial as partida_subparcial,
                        imp_par.tipo_moneda as tipo_moneda,
                        imp_par.codigo_economico as codigo_economico,
                        imp_par.finalidad as codn_final,
                        imp_par.funcion as codn_funci,
                        imp_par_liq.importe_liquida_partida as impp_conce,
                        dh89.codigoesc as tipoescalafon
                    INTO TEMP l
                    FROM
                        mapuche.dh22
                        INNER JOIN mapuche.imp_liquidaciones imp_liq ON dh22.nro_liqui = imp_liq.nro_liqui
                        INNER JOIN mapuche.imp_partida_liquidacion imp_par_liq ON imp_par_liq.id_liquidacion = imp_liq.id_liquidacion
                        INNER JOIN mapuche.imp_partidas imp_par ON imp_par.id_partida = imp_par_liq.id_partida
                        LEFT JOIN mapuche.dh89 ON dh89.codigoescalafon = imp_liq.codigoescalafon
                    WHERE
                        imp_liq.nro_liqui = ? AND
                        imp_liq.nro_orimp > 0 AND
                        imp_liq.codn_conce > 0
                    ORDER BY
                        imp_liq.codc_uacad";

        DB::connection($this->getConnectionName())->statement($sql, [$liquidacion]);
    }

    /**
     * Crea la tabla temporal che para el procesamiento de aportes y retenciones
     */
    protected function createCheTable(): void
    {
        $sql = "SELECT
        codn_area,
        codn_subar,
        tipo_conce,
        dh46.codn_grupo,
        LPAD(' ',50,' ') as desc_grupo,
        'S'::char(1) AS sino_cheque,
        sum(impp_conce::NUMERIC) as importe
    INTO TEMP che
    FROM
        l
        LEFT OUTER JOIN mapuche.dh46 ON l.codn_conce = dh46.cod_conce
    GROUP BY
        codn_area,
        codn_subar,
        tipo_conce,
        codn_grupo
    ORDER BY
        codn_area,
        codn_subar;";

        DB::connection($this->getConnectionName())->statement($sql);
    }

    /**
     * Actualiza las descripciones de grupo en la tabla che
     */
    protected function updateCheGroupDescriptions(): void
    {
        $sql = "UPDATE che
            SET desc_grupo = dh45.desc_grupo
            FROM
                mapuche.dh45
            WHERE
                che.codn_grupo = dh45.codn_grupo;";

        DB::connection($this->getConnectionName())->statement($sql);

        $sql = "UPDATE che
            SET sino_cheque = 'N'
            FROM
                mapuche.dh45
            WHERE
                che.codn_grupo = dh45.codn_grupo AND
                not(dh45.sino_sipefco);";

        DB::connection($this->getConnectionName())->statement($sql);
    }

    /**
     * Crea la tabla temporal d21tmp con información de cargos
     */
    protected function createD21TempTable(): void
    {
        $sql = "SELECT
        l.*,
        dh03.codc_carac
    INTO TEMP d21tmp
    FROM
        l,
        mapuche.dh03
    WHERE
        l.nro_cargo = dh03.nro_cargo AND
        l.nro_legaj = dh03.nro_legaj;";

        DB::connection($this->getConnectionName())->statement($sql);
    }

    /**
     * Crea la tabla temporal l2 con información adicional
     */
    protected function createL2TempTable(): void
    {
        $sql = "SELECT
        d21tmp.*,
        dh89.tipo_perm_tran AS tipo_carac
    INTO TEMP l2
    FROM
        d21tmp,
        mapuche.dh89
    WHERE
        d21tmp.codigoescalafon = dh89.codigoescalafon;";

        DB::connection($this->getConnectionName())->statement($sql);
    }

    /**
     * Obtiene los netos liquidados
     */
    public function getNetosLiquidados(): array
    {
        return [
            'neto_liquidado' => $this->llenaBlancoIzq(
                number_format($this->netos, 2, '.', ''),
                16
            )
        ];
    }

    /**
     * Genera el contenido del archivo CHE
     */
    public function generateCheContent(array $liquidaciones, int $anio, int $mes, int $indice): array
    {
        $grupo_aportes_retenciones = [];
        $aportes = $this->getAportes();

        foreach ($aportes as $aporte) {
            $grupo_aportes_retenciones[] = [
                "codigo" => $this->llenaImportes($aporte['grupo'], 3),
                "descripcion" => $this->llenaBlancos($aporte['desc_grupo'], 50),
                "importe" => $this->llenaBlancoIzq(
                    number_format($aporte['total'], 2, '.', ''),
                    16
                )
            ];
        }

        return [
            'neto_liquidado' => $this->llenaBlancoIzq(
                number_format($this->netos, 2, '.', ''),
                16
            ),
            'accion' => 'O',
            'grupo_aportes_retenciones' => $grupo_aportes_retenciones
        ];
    }

    /**
     * Obtiene los aportes y retenciones agrupados
     */
    public function getAportes(): array
    {
        //$this->escribir_log("info", "Obteniendo aportes.");
        $sql = "UPDATE che SET importe = -importe WHERE tipo_conce = 'D';";
        // toba::db()->ejecutar($sql);
        DB::connection($this->getConnectionName())->execute($sql);
        $sql = "SELECT
					codn_grupo AS grupo,
					desc_grupo,
					sino_cheque,
					sum(importe) AS total
				FROM
					che
				WHERE
					codn_grupo is not null AND
					(tipo_conce = 'A' OR
					tipo_conce = 'D')
				GROUP BY
					codn_grupo,
					desc_grupo,
					sino_cheque
				ORDER BY
					codn_grupo;";
        return DB::connection($this->getConnectionName())->select($sql);
    }

    /**
     * Actualiza los importes negativos para tipo de concepto D
     */
    protected function updateImportesNegativos(): void
    {
        DB::connection($this->getConnectionName())
            ->table('che')
            ->where('tipo_conce', 'D')
            ->update(['importe' => DB::raw('-importe')]);
    }

    /**
     * Inserta los datos generados en la tabla de la base de datos.
     */
    private function insertIntoDatabase(): void
    {
        $aportes = $this->getAportes(); // Obtener los aportes generados

        foreach ($aportes as $aporte) {
            \App\Models\ComprobanteNominaModel::create([
                'anio_periodo' => date('Y'), // Asumiendo que el año es el actual
                'mes_periodo' => date('m'), // Asumiendo que el mes es el actual
                'nro_liqui' => $aporte['nro_liqui'], // Asegúrate de que este campo esté disponible en $aporte
                'desc_liqui' => $aporte['desc_grupo'], // O el campo que corresponda
                'tipo_pago' => 'CHE', // O el tipo de pago que corresponda
                'importe' => $this->llenaBlancoIzq(
                    number_format($aporte['total'], 2, '.', ''),
                    16
                ),
                'area_administrativa' => $aporte['area_administrativa'], // Asegúrate de que este campo esté disponible
                'subarea_administrativa' => $aporte['subarea_administrativa'], // Asegúrate de que este campo esté disponible
                'numero_retencion' => $aporte['numero_retencion'] ?? null, // Si aplica
                'descripcion_retencion' => $aporte['descripcion_retencion'] ?? null, // Si aplica
                'requiere_cheque' => $aporte['requiere_cheque'] ?? false, // Si aplica
                'codigo_grupo' => $aporte['codigo_grupo'] ?? null, // Si aplica
            ]);
        }

        // También puedes insertar los netos liquidados
        \App\Models\ComprobanteNominaModel::create([
            'anio_periodo' => date('Y'),
            'mes_periodo' => date('m'),
            'nro_liqui' => null, // O el número de liquidación correspondiente
            'desc_liqui' => 'Neto Liquidado',
            'tipo_pago' => 'CHE',
            'importe' => $this->llenaBlancoIzq(
                number_format($this->netos, 2, '.', ''),
                16
            ),
            'area_administrativa' => null, // O el valor correspondiente
            'subarea_administrativa' => null, // O el valor correspondiente
            'numero_retencion' => null,
            'descripcion_retencion' => null,
            'requiere_cheque' => false,
            'codigo_grupo' => null,
        ]);
    }
}
