<?php
declare(strict_types=1);

namespace App\Services\Reportes;

use App\Models\Dh21;
use App\Models\Mapuche\Embargo;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Collection as CollectionEloquent;

class EmbargoReportService
{
    public function generateReport(int $nro_liqui = 3): Collection
    {
        // Primero obtenemos los embargos activos
        $embargos = Embargo::query()
            ->with(['datosPersonales', 'tipoEmbargo', 'datosPersonales.dh03'])
            ->whereHas('estado', fn($query) => $query->where('id_estado_embargo', 2))
            // ->where('nro_legaj', '=', 149639)
            ->get();

            // Agrupamos por legajo y calculamos importes
        return $this->getEmbargos($embargos, $nro_liqui);
    }

    /**
     * @param CollectionEloquent $embargos
     * @param int $nro_liqui
     * @return CollectionEloquent|Collection
     */
    public function getEmbargos(CollectionEloquent $embargos, int $nro_liqui): Collection|CollectionEloquent
    {
        return $embargos
            ->groupBy('nro_legaj')
            ->map(function ($embargosLegajo) use ($nro_liqui) {
                return $embargosLegajo->map(function ($embargo) use ($nro_liqui) {

                    // Obtenemos los importes descontados por cargo
                    $importes = $embargo->getImporteDescontado($nro_liqui);

                    // Mapeamos cada cargo con su importe
                    return $importes->map(function ($importe) use ($embargo) {
                        return [
                            'nro_legaj' => $embargo->nro_legaj,
                            'nombre_completo' => $embargo->datosPersonales->nombre_completo,
                            'codn_conce' => $embargo->tipoEmbargo->codn_conce,
                            'importe_descontado' => $importe->impp_conce,
                            'nro_embargo' => $embargo->nro_embargo,
                            'nro_cargo' => $importe->nro_cargo,
                            'caratula' => $embargo->caratula,
                            'codc_uacad' => $embargo->datosPersonales->dh03()
                                ->where('nro_cargo', $importe->nro_cargo)
                                ->value('codc_uacad')
                        ];
                    });
                })->flatten(1);
            });
    }
}
