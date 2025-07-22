<?php

declare(strict_types=1);

namespace App\Services;

use App\Traits\MapucheConnectionTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RepEmbarazadaService
{
    use MapucheConnectionTrait;

    /**
     * Servicio para gestionar la tabla de embarazadas.
     *
     * Esta clase proporciona métodos para administrar la tabla suc.rep_embarazadas,
     * que almacena información sobre el personal en licencia por embarazo.
     * Los datos son extraídos de las siguientes tablas de Mapuche:
     * - dh21h: Licencias (codn_conce = 126 para embarazo)
     * - dh01: Datos personales
     * - dh03: Datos de unidad académica
     *
     * @package App\Services
     */
    public function ensureTableExists(): void
    {
        if (!Schema::connection($this->getConnectionName())->hasTable('suc.rep_embarazadas')) {
            DB::connection($this->getConnectionName())->statement('
                CREATE TABLE suc.rep_embarazadas (
                    nro_legaj  INTEGER,
                    apellido   CHAR(20),
                    nombre     CHAR(20),
                    cuil       TEXT,
                    codc_uacad CHAR(4)
                )
            ');
        }
    }

    /**
     * Poblar la tabla con datos de Mapuche.
     */
    public function populateTable(): bool
    {
        // Primero limpiamos la tabla
        DB::connection($this->getConnectionName())->table('suc.rep_embarazadas')->truncate();

        // Ejecutamos la consulta de inserción
        return DB::connection($this->getConnectionName())->statement('
            INSERT INTO suc.rep_embarazadas (nro_legaj, apellido, nombre, cuil, codc_uacad)
            WITH embarazadas AS (
                SELECT DISTINCT nro_legaj
                FROM mapuche.dh21h
                WHERE codn_conce = 126
            ),
            latest_dh03 AS (
                SELECT *,
                    ROW_NUMBER() OVER (PARTITION BY nro_legaj ORDER BY nro_legaj) as rn
                FROM mapuche.dh03
            )
            SELECT DISTINCT
                d3.nro_legaj,
                d1.desc_appat as apellido,
                d1.desc_nombr as nombre,
                concat(d1.nro_cuil1, d1.nro_cuil, d1.nro_cuil2) as cuil,
                d3.codc_uacad
            FROM embarazadas e
            JOIN mapuche.dh01 d1 ON e.nro_legaj = d1.nro_legaj
            LEFT JOIN latest_dh03 d3 ON d3.nro_legaj = d1.nro_legaj AND d3.rn = 1
            ORDER BY 1
        ');
    }

    /**
     * Vaciar la tabla.
     */
    public function truncateTable(): void
    {
        DB::connection($this->getConnectionName())->table('suc.rep_embarazadas')->truncate();
    }
}
