<?php

namespace App\Repositories;

use App\Models\Mapuche\MapucheConfig;
use App\Traits\MapucheConnectionTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class NominaRepository
{
    use MapucheConnectionTrait;

    protected $connection;

    protected $schema;

    public function __construct()
    {
        try {
            $this->connection = $this->getConnectionFromTrait();
            $this->schema = Schema::connection($this->connection->getName());
        } catch (\Exception $e) {
            throw new \RuntimeException('No se pudo establecer la conexión a la base de datos: ' . $e->getMessage());
        }
    }

    public function __destruct()
    {
        try {
            $this->dropTemporaryTables();
        } catch (\Exception $e) {
            Log::error('Error al limpiar tablas temporales: ' . $e->getMessage());
        }
    }

    public function createLiquidationTable(int $nroLiqui): void
    {
        switch (MapucheConfig::getParametrosAjustesImpContable()) {
            case 'Deshabilitada':
                try {
                    DB::connection($this->connection->getName())->beginTransaction();

                    $this->dropTable('l');

                    $query = DB::connection($this->connection->getName())
                        ->table('mapuche.dh21')
                        ->join('mapuche.dh22', 'dh22.nro_liqui', '=', 'dh21.nro_liqui')
                        ->where('dh21.nro_liqui', $nroLiqui)
                        ->where('dh21.nro_orimp', '>', 0)
                        ->where('dh21.codn_conce', '>', 0)
                        ->orderBy('dh21.codc_uacad');

                    DB::connection($this->connection->getName())->statement(
                        'CREATE TEMPORARY TABLE l AS ' . $query->toSql(),
                        $query->getBindings(),
                    );
                    Log::info('Tabla l creada exitosamente', [$query->toSql(), $query->getBindings()]);
                    DB::connection($this->connection->getName())->commit();
                } catch (\Exception $e) {
                    DB::connection($this->connection->getName())->rollBack();
                    throw $e;
                }
                break;
            default:
                throw new \RuntimeException('No se encontraron parámetros de ajustes de contabilidad.');
        }
    }

    public function createCheTable(): void
    {
        $this->dropTable('che');

        $this->schema->create('che', function ($table): void {
            $table->temporary();
            $table->string('codn_area')->nullable();
            $table->string('codn_subar')->nullable();
            $table->char('tipo_conce', 1)->nullable();
            $table->integer('codn_grupo')->nullable();
            $table->string('desc_grupo', 50)->nullable();
            $table->char('sino_cheque', 1)->nullable();
            $table->decimal('importe', 15, 2)->nullable();
        });
    }

    public function insertInitialCheData(): void
    {
        try {
            DB::connection($this->connection->getName())->beginTransaction();

            $defaultDescription = config('nomina.default_group_description', str_pad('SIN DESCRIPCIÓN', 50, ' '));

            DB::connection($this->connection->getName())
                ->table('che')
                ->insertUsing(
                    [
                        'codn_area', 'codn_subar', 'tipo_conce', 'codn_grupo',
                        'desc_grupo', 'sino_cheque', 'importe',
                    ],
                    function ($query) use ($defaultDescription): void {
                        $query->from('l')
                            ->leftJoin('dh46', 'l.codn_conce', '=', 'dh46.cod_conce')
                            ->select([
                                'l.codn_area',
                                'l.codn_subar',
                                'l.tipo_conce',
                                'dh46.codn_grupo',
                                DB::raw('?::varchar as desc_grupo'),
                                DB::raw("'S'::char(1) AS sino_cheque"),
                                DB::raw('sum(l.impp_conce::NUMERIC) as importe'),
                            ])
                            ->groupBy('l.codn_area', 'l.codn_subar', 'l.tipo_conce', 'dh46.codn_grupo')
                            ->setBindings([$defaultDescription]);
                    },
                );

            DB::connection($this->connection->getName())->commit();
        } catch (\Exception $e) {
            DB::connection($this->connection->getName())->rollBack();
            throw $e;
        }
    }

    public function updateDescriptions(): void
    {
        DB::connection($this->connection->getName())
            ->table('che')
            ->join('dh45', 'che.codn_grupo', '=', 'dh45.codn_grupo')
            ->update(['che.desc_grupo' => DB::raw('dh45.desc_grupo')]);
    }

    public function getAportes(): array
    {
        $this->updateImportesNegativos();

        return DB::connection($this->connection->getName())
            ->table('che')
            ->selectRaw("
                lpad((codn_area::int)::varchar,2,'0') AS area,
                lpad((codn_subar::int)::varchar,2,'0') AS subarea,
                codn_grupo AS grupo,
                desc_grupo,
                sino_cheque,
                sum(importe) AS total
            ")
            ->whereNotNull('codn_grupo')
            ->whereIn('tipo_conce', ['A', 'D'])
            ->groupBy('codn_area', 'codn_subar', 'codn_grupo', 'desc_grupo', 'sino_cheque')
            ->orderBy('codn_grupo')
            ->get()
            ->toArray();
    }

    public function getNetosLiquidados(): array
    {
        $this->updateImportesNegativos();

        return DB::connection($this->connection->getName())
            ->table('che')
            ->selectRaw("
                lpad((codn_area::int)::varchar,2,'0') AS area,
                lpad((codn_subar::int)::varchar,2,'0') AS subarea,
                sum(importe) AS netos
            ")
            ->where('tipo_conce', '<>', 'A')
            ->groupBy('codn_area', 'codn_subar')
            ->orderBy('codn_area')
            ->orderBy('codn_subar')
            ->get()
            ->toArray();
    }

    public function dropTemporaryTables(): void
    {
        $this->dropTable('l');
        $this->dropTable('che');
    }

    protected function updateImportesNegativos(): void
    {
        DB::connection($this->connection->getName())
            ->table('che')
            ->where('tipo_conce', 'D')
            ->update(['importe' => DB::raw('-importe')]);
    }

    protected function dropTable(string $table): void
    {
        if ($this->schema->hasTable($table)) {
            $this->schema->drop($table);
        }
    }
}
