<?php

declare(strict_types=1);

namespace App\Repositories\Afip;

use App\Traits\MapucheConnectionTrait;
use Illuminate\Support\Facades\DB;

class SicossCpto205Repository
{
    use MapucheConnectionTrait;

    public function procesarConceptos(array $liquidaciones): int
    {
        return DB::connection($this->getConnectionName())->transaction(function () use ($liquidaciones) {
            // 1. Crear tabla temporal para cargos asociados
            $this->crearTablaCargosAsociados($liquidaciones);

            // 2. Crear tabla temporal para cargos autónomos
            $this->crearTablaCargosAutoridades($liquidaciones);

            // 3. Crear tabla temporal para cargos nodocentes
            $this->crearTablaCargosNodocentes($liquidaciones);

            // 4. Crear tabla temporal con la unión de resultados
            $this->crearTablaResultados();

            // 5. Calcular montos y actualizar tabla final
            $this->calcularMontosYActualizar($liquidaciones);

            return $this->contarRegistros();
        });
    }

    /**
     * Elimina la tabla temporal si existe.
     */
    public function eliminarTablaTemporal(): void
    {
        DB::connection($this->getConnectionName())->statement('
            DROP TABLE IF EXISTS resultado_cargos_asoc;
            DROP TABLE IF EXISTS resultado_cargos_aut;
            DROP TABLE IF EXISTS resultado_cargos_nod;
            DROP TABLE IF EXISTS tcpto;
            DROP TABLE IF EXISTS tcpto205;
        ');
    }


    private function crearTablaCargosAsociados(array $liquidaciones): void
    {
        $liquidacionesStr = implode(',', $liquidaciones);

        DB::connection($this->getConnectionName())->statement("
            CREATE TEMP TABLE resultado_cargos_asoc AS
            SELECT DISTINCT d.nro_legaj, d.nro_cargo, d2.codc_categ, d2.codc_dedic,
                   d2.codigoescalafon, d1.codc_uacad, d.coddependesemp
            FROM mapuche.dh03 d
            JOIN mapuche.dh21 d1 ON d.nro_cargo = d1.nro_cargo
            JOIN mapuche.dh11 d2 ON d.codc_categ = d2.codc_categ
            WHERE d1.nro_liqui IN ({$liquidacionesStr})
            AND d2.codigoescalafon = 'DOCE'
            AND d2.codc_dedic IN ('EXCL', 'SEMI')
            AND d2.codc_categ in ('TITE','TITS', 'TITP','ASOE','ASOS','ASOP',
                                'ADJE','ADJS','ADJP','JTPE','JTPS','JTPS')
            AND d1.codn_conce = 205
            ORDER BY 2
        ");
    }

    private function crearTablaCargosAutoridades(array $liquidaciones): void
    {
        $liquidacionesStr = implode(',', $liquidaciones);

        DB::connection($this->getConnectionName())->statement("
            CREATE TEMP TABLE resultado_cargos_aut AS
            SELECT DISTINCT d.nro_legaj, d.nro_cargo, d2.codc_categ, d2.codc_dedic,
                   d2.codigoescalafon, d1.codc_uacad, d.coddependesemp
            FROM mapuche.dh03 d
            JOIN mapuche.dh21 d1 ON d.nro_cargo = d1.nro_cargo
            JOIN mapuche.dh11 d2 ON d.codc_categ = d2.codc_categ
            JOIN resultado_cargos_asoc a ON d.nro_legaj = a.nro_legaj
            WHERE d1.nro_liqui IN ({$liquidacionesStr})
            AND d2.codigoescalafon = 'AUTO'
            AND d2.codc_categ in ('SEFE','SEFC','SEFP','SEUE','SEUC','SEUP')
            ORDER BY 2
        ");
    }

    private function crearTablaCargosNodocentes(array $liquidaciones): void
    {
        $liquidacionesStr = implode(',', $liquidaciones);

        DB::connection($this->getConnectionName())->statement("
            CREATE TEMP TABLE resultado_cargos_nod AS
            SELECT DISTINCT d.nro_legaj, d.nro_cargo, d2.codc_categ, d2.codc_dedic,
                   d2.codigoescalafon, d1.codc_uacad, d.coddependesemp
            FROM mapuche.dh03 d
            JOIN mapuche.dh21 d1 ON d.nro_cargo = d1.nro_cargo
            JOIN mapuche.dh11 d2 ON d.codc_categ = d2.codc_categ
            JOIN resultado_cargos_asoc a ON d.nro_legaj = a.nro_legaj
            WHERE d1.nro_liqui IN ({$liquidacionesStr})
            AND d2.codigoescalafon = 'NODO'
            ORDER BY 2
        ");
    }

    /**
     * Crea tabla temporal con la unión de resultados.
     */
    private function crearTablaResultados(): void
    {
        DB::connection($this->getConnectionName())->statement('
            CREATE TEMP TABLE tcpto AS
            SELECT nro_legaj FROM resultado_cargos_aut
            UNION ALL
            SELECT nro_legaj FROM resultado_cargos_nod
            ORDER BY nro_legaj
        ');
    }

    private function calcularMontosYActualizar(array $liquidaciones): void
    {
        $liquidacionesStr = implode(',', $liquidaciones);

        // Primero creamos la tabla temporal con los montos
        DB::connection($this->getConnectionName())->statement("
            CREATE TEMP TABLE tcpto205 AS
            SELECT mapuche.dh21.nro_legaj, c.cuil,
                   ((SUM(impp_conce) * 100) / 2)::NUMERIC(10, 2) AS monto
            FROM mapuche.dh21,
                mapuche.vdh01 c,
                tcpto b
            WHERE nro_liqui IN ({$liquidacionesStr})
                AND mapuche.dh21.nro_legaj = c.nro_legaj
                AND mapuche.dh21.nro_legaj = b.nro_legaj
                AND codn_conce = 789
                AND mapuche.dh21.nro_legaj IN
                    (SELECT DISTINCT nro_legaj
                        FROM mapuche.dh21
                        WHERE nro_liqui IN ({$liquidacionesStr})
                        AND codn_conce = '205')
            GROUP BY mapuche.dh21.nro_legaj, c.cuil
            ORDER BY nro_legaj
        ");

        // Luego actualizamos la tabla final
        DB::connection($this->getConnectionName())->statement('
            UPDATE suc.afip_mapuche_sicoss
            SET cpto_no_remun = cpto_no_remun - b.monto,
                sueldo_adicc = sueldo_adicc + b.monto,
                rem_impo1 = CASE WHEN rem_total < rem_impo1 THEN rem_impo1 + b.monto ELSE rem_impo1 END,
                rem_impo2 = rem_impo2 + b.monto,
                rem_impo3 = rem_impo3 + b.monto,
                rem_dec_788 = rem_dec_788 + b.monto
            FROM tcpto205 b
            WHERE b.cuil = suc.afip_mapuche_sicoss.cuil
            AND suc.afip_mapuche_sicoss.rem_impo1 <= (
                SELECT importe * 1
                FROM mapuche.constante_unica
                WHERE id_constante = (
                    SELECT max(id_constante)
                    FROM mapuche.constante
                    WHERE nombre = \'TOPAMAX\'
                )
            )
        ');
    }




    /**
     * Cuenta los registros en la tabla temporal
     *
     * @return int Número de registros
     */
    public function contarRegistros(): int
    {
        $resultado = DB::connection($this->getConnectionName())->selectOne("SELECT COUNT(*) as total FROM tcpto205");
        return (int) $resultado->total;
    }



    /**
     * Inicia una transacción en la conexión Mapuche
     *
     * Este método inicia una nueva transacción en la base de datos,
     * permitiendo realizar múltiples operaciones que serán confirmadas
     * o revertidas como una unidad atómica.
     *
     * @return void
     */
    public function iniciarTransaccion(): void
    {
        DB::connection($this->getConnectionName())->beginTransaction();
    }


    /**
     * Confirma una transacción en la conexión Mapuche
     *
     * Este método confirma todos los cambios realizados dentro de la transacción actual
     * y los hace permanentes en la base de datos.
     *
     * @return void
     */
    public function confirmarTransaccion(): void
    {
        DB::connection($this->getConnectionName())->commit();
    }


    /**
     * Revierte una transacción en la conexión Mapuche
     *
     * Este método deshace todos los cambios realizados dentro de la transacción actual
     * y restaura el estado de la base de datos al punto anterior al inicio de la transacción.
     *
     * @return void
     */
    public function revertirTransaccion(): void
    {
        DB::connection($this->getConnectionName())->rollBack();
    }
}
