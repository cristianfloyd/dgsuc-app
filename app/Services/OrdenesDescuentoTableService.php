<?php

namespace App\Services;

use App\Contracts\Tables\OrdenesDescuentoTableDefinition;
use App\Services\Abstract\AbstractTableService;
use App\Traits\Mapuche\TableServiceTrait;
use App\Traits\MapucheConnectionTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class OrdenesDescuentoTableService extends AbstractTableService
{
    use MapucheConnectionTrait;
    use TableServiceTrait;

    private const TABLE_NAME = OrdenesDescuentoTableDefinition::TABLE;

    private OrdenesDescuentoTableDefinition $definition;

    public function __construct(OrdenesDescuentoTableDefinition $definition)
    {
        $this->definition = $definition;
    }

    public function getTableName(): string
    {
        return $this->definition->getTableName();
    }

    public function createAndPopulate(): void
    {
        try {
            DB::connection($this->getConnectionName())->transaction(function (): void {
                $this->createTableIfNotExists();
                $this->truncateTable();
                $this->populateTable();
                $this->updateLastSync();
            });

            Log::info('Tabla rep_ordenes_descuento creada y poblada exitosamente');
        } catch (\Exception $e) {
            Log::error('Error en createAndPopulate', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    public function populateTable(): void
    {
        DB::connection($this->getConnectionName())->statement($this->getTablePopulationQuery());
    }

    public function exists(): bool
    {
        return Schema::connection($this->getConnectionName())->hasTable(self::TABLE_NAME);
    }

    /**
     * @inheritDoc
     */
    protected function getTableDefinition(): array
    {
        return $this->definition->getColumns();
    }

    /**
     * @inheritDoc
     */
    protected function getIndexes(): array
    {
        return $this->definition->getIndexes();
    }

    /**
     * @inheritDoc
     */
    protected function getTablePopulationQuery(): string
    {
        return '
            INSERT INTO ' . self::TABLE_NAME . " (
                id,
                nro_liqui,
                desc_liqui,
                codc_uacad,
                desc_item,
                codn_funci,
                caracter,
                tipoescalafon,
                codn_fuent,
                nro_inciso,
                codn_progr,
                codn_conce,
                desc_conce,
                impp_conce,
                created_at,
                updated_at
            )
            SELECT DISTINCT
                nextval('suc.rep_ordenes_descuento_id_seq'), -- Secuencia para el ID
                dh21.nro_liqui,
                dh22.desc_liqui,
                dh21.codc_uacad,
                dh30.desc_item,
                dh21.codn_funci,
                CASE WHEN dh03.codc_carac IN ( 'PERM', 'PLEN', 'REGU' ) THEN 'PERM' ELSE 'CONT' END AS caracter,
                dh21.tipoescalafon,
                dh21.codn_fuent,
                COALESCE( CASE WHEN dh35.tipo_carac = 'T' THEN CASE WHEN ((SUBSTR( dh17.objt_gtote, 1, 1 )::INT < 1) OR
                                                                          (SUBSTR( dh17.objt_gtote, 1, 1 )::INT > 5))
	                                                                    THEN 1
	                                                                    ELSE SUBSTR( dh17.objt_gtote, 1, 1 )::INT END
                                                          ELSE CASE WHEN ((SUBSTR( dh17.objt_gtope, 1, 1 )::INT < 1) OR
                                                                          (SUBSTR( dh17.objt_gtope, 1, 1 )::INT > 5))
	                                                                    THEN 1
	                                                                    ELSE SUBSTR( dh17.objt_gtope, 1, 1 )::INT END END,
                          1 )                                                                       AS nro_inciso,
                dh21.codn_progr,
                dh21.codn_conce,
                dh12.desc_conce,
                sum(dh21.impp_conce)::NUMERIC(15,2) AS importe,
                CURRENT_TIMESTAMP,
                CURRENT_TIMESTAMP
FROM mapuche.dh21 dh21
	     LEFT OUTER JOIN mapuche.dh03 ON (dh03.nro_legaj = dh21.nro_legaj AND dh03.nro_cargo = dh21.nro_cargo)
	     JOIN mapuche.dh22 ON (dh22.nro_liqui = dh21.nro_liqui)
	     JOIN mapuche.dh30 ON (dh30.nro_tabla = 13 AND dh30.desc_abrev = dh21.codc_uacad)
	     JOIN mapuche.dh12 ON (dh12.codn_conce = dh21.codn_conce)
	     LEFT OUTER JOIN mapuche.dh17 ON (dh17.codn_conce = dh21.codn_conce)
	     LEFT OUTER JOIN mapuche.dh35 ON (
	(dh35.tipo_escal = dh21.tipoescalafon OR (dh21.tipoescalafon = 'C' AND dh35.tipo_escal = 'S')) AND
	dh35.codc_carac = dh03.codc_carac)
WHERE dh21.codn_conce / 100 IN (2,3)
GROUP BY dh21.nro_liqui,
         dh22.desc_liqui,
         dh21.codc_uacad,
         dh30.desc_item,
         dh21.codn_funci,
         CASE WHEN dh03.codc_carac IN ( 'PERM', 'PLEN', 'REGU' ) THEN 'PERM' ELSE 'CONT' END,
         dh21.tipoescalafon,
         dh21.codn_fuent,
         COALESCE( CASE WHEN dh35.tipo_carac = 'T' THEN CASE WHEN ((SUBSTR( dh17.objt_gtote, 1, 1 )::INT < 1) OR
                                                                   (SUBSTR( dh17.objt_gtote, 1, 1 )::INT > 5)) THEN 1
                                                                                                               ELSE SUBSTR( dh17.objt_gtote, 1, 1 )::INT END
                                                   ELSE CASE WHEN ((SUBSTR( dh17.objt_gtope, 1, 1 )::INT < 1) OR
                                                                   (SUBSTR( dh17.objt_gtope, 1, 1 )::INT > 5)) THEN 1
                                                                                                               ELSE SUBSTR( dh17.objt_gtope, 1, 1 )::INT END END,
                   1 ),
         dh21.codn_progr,
         dh21.codn_conce,
         dh12.desc_conce
ORDER BY dh21.codc_uacad, dh21.codn_conce;
        ";
    }

    protected function createIndexes(): void
    {
        $connection = $this->getConnectionName();

        foreach (OrdenesDescuentoTableDefinition::INDEXES as $name => $columns) {
            $indexName = "suc_rep_ordenes_descuento_{$name}_index";

            // Verificar si el índice ya existe
            $indexExists = DB::connection($connection)
                ->select("SELECT to_regclass('suc.{$indexName}') IS NOT NULL as exists")[0]->exists;

            if (!$indexExists) {
                Schema::connection($connection)->table(self::TABLE_NAME, function ($table) use ($columns, $name): void {
                    $table->index($columns, $name);
                });
            }
        }
    }

    protected function addColumn($table, $column, $definition): void
    {
        switch ($definition['type']) {
            case 'id':
                $table->id();
                break;
            case 'string':
                $table->string($column, $definition['length'] ?? null);
                break;
            case 'integer':
                $table->integer($column);
                break;
            case 'decimal':
                $table->decimal($column, $definition['precision'], $definition['scale']);
                break;
            case 'date':
                $table->date($column);
                break;
            case 'timestamp':
                $table->timestamp($column)->useCurrent();
                break;
        }
    }

    private function updateLastSync(): void
    {
        DB::connection($this->getConnectionName())->table(self::TABLE_NAME)->update(['last_sync' => now()]);
    }

    private function truncateTable(): void
    {
        DB::connection($this->getConnectionName())->table(self::TABLE_NAME)->truncate();
    }

    private function createTableIfNotExists(): void
    {
        if (!Schema::connection($this->getConnectionName())->hasTable('suc.rep_ordenes_descuento')) {
            Schema::connection($this->getConnectionName())->create('suc.rep_ordenes_descuento', function ($table): void {
                $this->addLaravelPrimaryKey($table);
                foreach (OrdenesDescuentoTableDefinition::COLUMNS as $column => $definition) {
                    if ($column !== 'id') {
                        $this->addColumn($table, $column, $definition);
                    }
                }
            });

            // Mover la creación de índices fuera del callback de create
            $this->createIndexes();
        }
    }
}
