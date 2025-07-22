<?php

declare(strict_types=1);

namespace App\Services\Reportes;

use App\Repositories\Interfaces\FallecidoRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class FallecidoService
{
    public function __construct(
        private readonly FallecidoRepositoryInterface $repository,
    ) {
    }

    /**
     * Crea la tabla rep_fallecidos si no existe.
     */
    public function createTableIfNotExists(): void
    {
        if (!Schema::hasTable('rep_fallecidos')) {
            Schema::create('rep_fallecidos', function ($table): void {
                $table->integer('nro_legaj');
                $table->char('apellido', 20);
                $table->char('nombre', 20);
                $table->text('cuil');
                $table->char('codc_uacad', 4);
                $table->date('fec_defun')->nullable();
            });
        }
    }

    /**
     * Trunca la tabla rep_fallecidos.
     */
    public function truncateTable(): void
    {
        DB::table('rep_fallecidos')->truncate();
    }

    /**
     * Pobla la tabla con datos desde Mapuche.
     */
    public function populateFromMapuche(): void
    {
        DB::statement("
            INSERT INTO suc.rep_fallecidos (nro_legaj, apellido, nombre, cuil, codc_uacad, fec_defun)
            WITH fallecidos AS (
                SELECT DISTINCT d9.nro_legaj, d9.fec_defun
                FROM mapuche.dh09 d9
                WHERE d9.fec_defun IS NOT NULL
                AND d9.fec_defun >= '2024-12-01'
            ),
            latest_dh03 AS (
                SELECT *, ROW_NUMBER() OVER (PARTITION BY nro_legaj ORDER BY nro_legaj) AS rn
                FROM dh03
            )
            SELECT DISTINCT
                d3.nro_legaj,
                d1.desc_appat AS apellido,
                d1.desc_nombr AS nombre,
                CONCAT(d1.nro_cuil1, d1.nro_cuil, d1.nro_cuil2) AS cuil,
                d3.codc_uacad,
                e.fec_defun
            FROM fallecidos e
            JOIN dh01 d1 ON e.nro_legaj = d1.nro_legaj
            LEFT JOIN latest_dh03 d3 ON d3.nro_legaj = d1.nro_legaj AND d3.rn = 1
            ORDER BY 1
        ");
    }

    /**
     * Ejecuta el proceso completo de actualización.
     */
    public function refreshData(): void
    {
        DB::transaction(function (): void {
            $this->createTableIfNotExists();
            $this->truncateTable();
            $this->populateFromMapuche();
        });
    }
}
