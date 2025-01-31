<?php

namespace Tests\Unit\Services;

use PHPUnit\Framework\TestCase;
use Illuminate\Support\Facades\DB;
use App\Traits\MapucheConnectionTrait;
use Illuminate\Support\Facades\Schema;
use App\Services\RepGerencialFinalService;
use App\Services\Mapuche\PeriodoFiscalService;

class RepGerencialFinalServiceTest extends TestCase
{
    use MapucheConnectionTrait;

    public function getTable($table = null): string
    {
        if ($table === null) {
            return $this->getMapucheTable();
        }
        return $table;
    }

    private RepGerencialFinalService $service;
    private string $connection;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new RepGerencialFinalService(new PeriodoFiscalService());
        $this->connection = $this->getConnectionName();
    }

    /** @test */
    public function it_creates_all_required_tables()
    {
        $liquidaciones = [1, 2];

        $this->service->processReport($liquidaciones);

        $tables = [
            'rep_ger_datos_base_dh21',
            'rep_ger_importes_netos_c',
            'rep_ger_importes_hs_extras_c',
            'rep_ger_importes_netos_s',
            'rep_ger_importes_netos_o',
            'rep_ger_importes_netos_f',
            'rep_ger_importes_netos_d',
            'rep_ger_importes_netos_a',
            'rep_ger_importes_netos'
        ];

        foreach ($tables as $table) {
            $this->assertTrue(
                Schema::connection($this->connection)->hasTable("suc.{$table}"),
                "La tabla {$table} no fue creada"
            );
        }
    }

    /** @test */
    public function it_processes_data_for_given_liquidaciones()
    {
        $liquidaciones = [1, 2];

        $this->service->processReport($liquidaciones);

        $result = DB::connection($this->connection)
            ->table('suc.rep_ger_importes_netos')
            ->whereIn('nro_liqui', $liquidaciones)
            ->count();

        $this->assertGreaterThan(0, $result);
    }

    /** @test */
    public function it_calculates_correct_net_amounts()
    {
        $liquidaciones = [1];

        $this->service->processReport($liquidaciones);

        $result = DB::connection($this->connection)
            ->table('suc.rep_ger_importes_netos')
            ->select([
                'imp_bruto',
                'imp_neto',
                'imp_dctos',
                'imp_aport'
            ])
            ->first();

        $this->assertEquals(
            $result->imp_bruto - $result->imp_dctos,
            $result->imp_neto
        );
    }
}
