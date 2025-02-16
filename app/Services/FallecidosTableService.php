<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\Tables\FallecidosTableDefinition;
use App\Services\Abstract\AbstractTableService;
use Illuminate\Support\Facades\DB;

class FallecidosTableService extends AbstractTableService
{
    public function __construct(
        private readonly FallecidosTableDefinition $definition
    ) {}

    public function getTableName(): string
    {
        return $this->definition->getTableName();
    }

    protected function getTableDefinition(): array
    {
        return $this->definition->getColumns();
    }

    protected function getIndexes(): array
    {
        return $this->definition->getIndexes();
    }

    protected function getTablePopulationQuery(): string
    {
        return "
            INSERT INTO {$this->getTableName()} (nro_legaj, apellido, nombre, cuil, codc_uacad, fec_defun)
            WITH fallecidos AS (
                SELECT DISTINCT d9.nro_legaj, d9.fec_defun
                FROM mapuche.dh09 d9
                WHERE d9.fec_defun IS NOT NULL
                AND d9.fec_defun >= '2024-12-01'
            ),
            latest_dh03 AS (
                SELECT *, ROW_NUMBER() OVER (PARTITION BY nro_legaj ORDER BY nro_legaj) AS rn
                FROM mapuche.dh03
            )
            SELECT DISTINCT
                d3.nro_legaj,
                d1.desc_appat AS apellido,
                d1.desc_nombr AS nombre,
                CONCAT(d1.nro_cuil1, d1.nro_cuil, d1.nro_cuil2) AS cuil,
                d3.codc_uacad,
                e.fec_defun
            FROM fallecidos e
            JOIN mapuche.dh01 d1 ON e.nro_legaj = d1.nro_legaj
            LEFT JOIN latest_dh03 d3 ON d3.nro_legaj = d1.nro_legaj AND d3.rn = 1
            ORDER BY 1
        ";
    }

    public function populateFromDate(string $fecha): void
    {
        $query = str_replace(
            "'2024-12-01'", // fecha hardcodeada en la consulta original
            "'{$fecha}'",   // fecha dinámica
            $this->getTablePopulationQuery()
        );

        DB::connection($this->getConnectionName())->statement($query);
    }

    public function truncateTable(): void
    {
        DB::connection($this->getConnectionName())
            ->table($this->getTableName())
            ->truncate();
    }
}
