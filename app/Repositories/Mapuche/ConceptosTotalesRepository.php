<?php

namespace App\Repositories\Mapuche;

use Illuminate\Support\Facades\DB;
use App\Traits\MapucheConnectionTrait;
use Illuminate\Support\Collection;
use App\Repositories\Interfaces\ConceptosTotalesRepositoryInterface;

class ConceptosTotalesRepository implements ConceptosTotalesRepositoryInterface
{
    use MapucheConnectionTrait;

    /**
     * Obtiene los totales por concepto para un período específico
     *
     * @param array $conceptos Códigos de conceptos a incluir
     * @param int $year Año del período
     * @param int $month Mes del período
     * @return Collection
     */
    public function getTotalesPorConcepto(array $conceptos, int $year, int $month): Collection
    {
        $connection = DB::connection($this->getConnectionName());

        return $connection->table(function ($query) {
            $query->from('mapuche.dh21')
                ->select('*')
                ->unionAll(
                    DB::table('mapuche.dh21h')->select('*')
                );
        }, 'h21')
            ->join('mapuche.dh12 as h12', 'h21.codn_conce', '=', 'h12.codn_conce')
            ->whereIn('h21.codn_conce', $conceptos)
            ->whereIn('h21.nro_liqui', function ($sub) use ($year, $month) {
                $sub->from('mapuche.dh22')
                    ->select('nro_liqui')
                    ->where('sino_genimp', true)
                    ->where('per_liano', $year)
                    ->where('per_limes', $month);
            })
            ->groupBy('h21.codn_conce', 'h12.desc_conce')
            ->orderBy('h21.codn_conce')
            ->select([
                'h21.codn_conce',
                'h12.desc_conce',
                DB::raw('SUM(impp_conce)::numeric(15,2) as importe')
            ])
            ->get();
    }

    /**
     * Obtiene los totales por concepto agrupados por tipo (debe/haber)
     *
     * @param array $conceptos Códigos de conceptos a incluir
     * @param int $year Año del período
     * @param int $month Mes del período
     * @return array Resultados agrupados por tipo de concepto
     */
    public function getTotalesPorConceptoAgrupados(array $conceptos, int $year, int $month): array
    {
        $resultados = $this->getTotalesPorConcepto($conceptos, $year, $month);

        $agrupados = [
            'haberes' => $resultados->filter(function ($item) {
                return in_array(substr($item->codn_conce, 0, 1), ['2', '4', '6', '8']);
            }),
            'descuentos' => $resultados->filter(function ($item) {
                return in_array(substr($item->codn_conce, 0, 1), ['3', '5', '7', '9']);
            }),
        ];

        return [
            'haberes' => $agrupados['haberes'],
            'descuentos' => $agrupados['descuentos'],
            'total_haberes' => $agrupados['haberes']->sum('importe'),
            'total_descuentos' => $agrupados['descuentos']->sum('importe'),
            'neto' => $agrupados['haberes']->sum('importe') - $agrupados['descuentos']->sum('importe')
        ];
    }
}
