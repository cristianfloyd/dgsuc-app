<?php

namespace App\Livewire;

use App\Traits\MapucheConnectionTrait;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class ReporteLiquidacion extends Component
{
    use WithPagination;
    use MapucheConnectionTrait;

    public $legajo;

    public $perPage = 5;

    public $search;

    public function updatingLegajo(): void
    {
        $this->resetPage();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    #[Computed(persist: true)]
    public function generarReporte()
    {
        $query = DB::connection($this->getConnectionName())->table('mapuche.dh01 as d')
            ->join('mapuche.dh03 as d2', 'd.nro_legaj', '=', 'd2.nro_legaj')
            ->join('mapuche.dhc9 as d8', 'd2.codc_agrup', '=', 'd8.codagrup')
            ->join('mapuche.dh11 as d4', 'd2.codc_categ', '=', 'd4.codc_categ')
            ->join('mapuche.dh89 as d7', 'd4.codigoescalafon', '=', 'd7.codigoescalafon')
            ->join('mapuche.dh21 as d3', function ($join): void {
                $join->on('d3.nro_legaj', '=', 'd2.nro_legaj')
                    ->on('d3.nro_cargo', '=', 'd2.nro_cargo');
            })
            ->join('mapuche.dh31 as d5', 'd4.codc_dedic', '=', 'd5.codc_dedic')
            ->join('mapuche.dh35 as d6', function ($join): void {
                $join->on('d4.tipo_escal', '=', 'd6.tipo_escal')
                    ->on('d2.codc_carac', '=', 'd6.codc_carac')
                    ->whereRaw('(d3.codn_conce/100 = 1 OR d3.codn_conce/300 = 1)');
            })
            ->where('d3.nro_liqui', 1)
            ->select([
                'd3.nro_liqui',
                'd3.nro_legaj AS id_legajo',
                'd3.nro_cargo AS numero_cargo',
                'd3.codc_uacad AS id_dependencia',
                'd4.codc_categ AS categoria',
                // 'd3.codn_conce AS concepto',
                // 'd3.impp_conce AS monto',
                DB::raw('CAST(SUM(d3.impp_conce) AS NUMERIC(10,2)) AS monto_total'),
                'd7.descesc AS id_escalafon',
                'd2.coddependesemp AS id_oficina_pago',
                'd3.codn_fuent AS id_fuente_financiamento',
                DB::raw("CONCAT(d.nro_cuil1, '-', d.nro_cuil, '-', d.nro_cuil2) AS cuil"),
                'd.desc_appat AS apellido',
                'd.fec_nacim AS fecha_nacimiento',
                'd.tipo_sexo AS id_sexo',
                'd8.descagrup AS id_agrupamiento',
                'd5.desc_dedic AS id_dedicacion',
                DB::raw('CONCAT(d4.desc_categ, d6.desc_grupo, d5.desc_dedic) AS tipo_cargo_descripcion'),
            ])
            ->groupBy([
                'd3.nro_liqui',
                'd3.nro_legaj',
                'd3.nro_cargo',
                'd3.codc_uacad',
                'd4.codc_categ',
                'd7.descesc',
                'd2.coddependesemp',
                'd3.codn_fuent',
                DB::raw("CONCAT(d.nro_cuil1, '-', d.nro_cuil, '-', d.nro_cuil2)"),
                'd.desc_appat',
                'd.fec_nacim',
                'd.tipo_sexo',
                'd8.descagrup',
                'd5.desc_dedic',
                DB::raw('CONCAT(d4.desc_categ, d6.desc_grupo, d5.desc_dedic)'),
            ])
            ->orderBy('d3.nro_legaj')
            ->orderBy('d3.nro_cargo')
            ->orderBy('d3.codc_uacad')
            ->orderBy('d4.codc_categ');
        // ->orderBy('d3.codn_conce')
        // ->paginate(10);
        if ($this->legajo) {
            $query->where('d3.nro_legaj', $this->legajo);
        }
        if ($this->search) {
            $query->where(
                function ($q): void {
                    $q->where('d.desc_appat', 'like', '%' . strtoupper($this->search) . '%')
                        ->orWhere('d3.nro_cargo', 'like', '%' . $this->search . '%');
                },
            );
        }
        return $query->paginate($this->perPage);
    }

    public function render()
    {
        // dd($this->generarReporte());
        return view('livewire.reporte-liquidacion', [
            'liquidaciones' => $this->generarReporte(),
        ]);
    }
}
