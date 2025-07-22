<?php

namespace App\Services;

use App\Traits\MapucheConnectionTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class ConceptoListadoTableService
{
    use MapucheConnectionTrait;

    private const TABLE_NAME = 'suc.rep_concepto_listado';

    public function createAndPopulate(): void
    {
        try {
            DB::connection($this->getConnectionName())->transaction(function (): void {
                $this->createTableIfNotExists();
                $this->truncateTable();
                $this->populateTable();
                $this->createIndexes();
                $this->updateLastSync();
            });

            Log::info('Tabla concepto_listado creada y poblada exitosamente');
        } catch (\Exception $e) {
            Log::error('Error en createAndPopulate', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    public function getLastSync(): ?\DateTime
    {
        $result = DB::connection($this->getConnectionName())
            ->table(self::TABLE_NAME)
            ->select('last_sync')
            ->orderBy('last_sync', 'desc')
            ->first();

        return $result ? new \DateTime($result->last_sync) : null;
    }

    public function getCount(): int
    {
        return DB::connection($this->getConnectionName())
            ->table(self::TABLE_NAME)
            ->count();
    }

    public function exists(): bool
    {
        return Schema::connection($this->getConnectionName())
            ->hasTable(self::TABLE_NAME);
    }

    public function drop(): void
    {
        if ($this->exists()) {
            Schema::connection($this->getConnectionName())
                ->drop(self::TABLE_NAME);
        }
    }

    private function createTableIfNotExists(): void
    {
        if (!Schema::connection($this->getConnectionName())->hasTable(self::TABLE_NAME)) {
            Schema::connection($this->getConnectionName())->create(self::TABLE_NAME, function ($table): void {
                // Campos idénticos a la vista materializada
                $table->bigInteger('id')->primary();
                $table->integer('nro_liqui');
                $table->string('desc_liqui');
                $table->string('periodo_fiscal', 6);
                $table->integer('nro_legaj');
                $table->integer('nro_cargo');
                $table->string('apellido');
                $table->string('nombre');
                $table->string('cuil', 11);
                $table->string('codc_uacad', 10);
                $table->integer('codn_conce');
                $table->decimal('impp_conce', 12, 2);
                $table->timestamp('last_sync')->useCurrent();

                // Índices iguales a la vista materializada
                $table->index('nro_legaj');
                $table->index('codc_uacad');
                $table->index('periodo_fiscal');
                $table->index('codn_conce');
                $table->index(['periodo_fiscal', 'nro_liqui']);
            });
        }
    }

    private function truncateTable(): void
    {
        DB::connection($this->getConnectionName())
            ->table(self::TABLE_NAME)
            ->truncate();
    }

    private function populateTable(): void
    {
        DB::connection($this->getConnectionName())->statement('
            INSERT INTO ' . self::TABLE_NAME . "
            SELECT
                ROW_NUMBER() OVER () AS id,
                d.nro_liqui,
                dh22.desc_liqui,
                CONCAT(dh22.per_liano, LPAD(dh22.per_limes::TEXT, 2, '0'::TEXT)) AS periodo_fiscal,
                d.nro_legaj,
                d.nro_cargo,
                dh01.desc_appat AS apellido,
                dh01.desc_nombr AS nombre,
                CONCAT(dh01.nro_cuil1, lpad(dh01.nro_cuil::text, '8','0'), dh01.nro_cuil2) AS cuil,
                d.codc_uacad,
                d.codn_conce,
                d.impp_conce,
                CURRENT_TIMESTAMP as last_sync
            FROM mapuche.dh21 d
            LEFT JOIN mapuche.dh01 ON d.nro_legaj = dh01.nro_legaj
            JOIN mapuche.dh22 ON d.nro_liqui = dh22.nro_liqui
            WHERE d.codn_conce/100 IN (1,2,3)
        ");
    }

    private function createIndexes(): void
    {
        // Índices adicionales si son necesarios
        DB::connection($this->getConnectionName())->statement('
            CREATE INDEX IF NOT EXISTS idx_rep_concepto_listado_compound ON suc.rep_concepto_listado (periodo_fiscal, codn_conce)
        ');
    }

    private function updateLastSync(): void
    {
        DB::connection($this->getConnectionName())
            ->table(self::TABLE_NAME)
            ->update(['last_sync' => now()]);
    }
}
