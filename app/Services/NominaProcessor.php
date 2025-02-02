<?php

namespace App\Services;

use App\Models\ComprobanteNominaModel;
use Illuminate\Support\Collection;

class NominaProcessor
{
    private $temporaryTableManager;
    private $netos = 0;

    public function __construct(TemporaryTableManager $temporaryTableManager)
    {
        $this->temporaryTableManager = $temporaryTableManager;
    }

    public function processAndStore(array $liquidaciones, int $anio, int $mes, int $nroLiqui): Collection
    {
        $comprobantes = new Collection();

        // Obtenemos los netos liquidados
        $netosLiquidados = $this->temporaryTableManager->getNetosLiquidados();

        // Obtenemos los aportes/retenciones
        $aportes = $this->temporaryTableManager->getAportes();

        // Procesamos cada aporte y creamos los registros
        foreach ($aportes as $aporte) {
            $comprobante = new ComprobanteNominaModel([
                'anio_periodo' => $anio,
                'mes_periodo' => $mes,
                'nro_liqui' => $nroLiqui,
                'importe' => $aporte->total,
                'numero_retencion' => $aporte->grupo,
                'descripcion_retencion' => $aporte->desc_grupo,
                'requiere_cheque' => $aporte->sino_cheque === 'S',
                'codigo_grupo' => $aporte->grupo,
                'tipo_pago' => 'R',
                'area_administrativa' => $aporte->area,
                'subarea_administrativa' => $aporte->subarea,
            ]);

            $comprobante->save();
            $comprobantes->push($comprobante);
        }

        // Procesamos los netos liquidados
        foreach ($netosLiquidados as $neto) {
            $comprobante = new ComprobanteNominaModel([
                'anio_periodo' => $anio,
                'mes_periodo' => $mes,
                'nro_liqui' => $liquidaciones[0],
                'importe' => $neto->netos,
                'area_administrativa' => $neto->area,
                'subarea_administrativa' => $neto->subarea,
                'tipo_pago' => 'N'
            ]);

            $comprobante->save();
            $comprobantes->push($comprobante);
        }

        return $comprobantes;
    }

    public function calculateTotals(array $netosLiquidados): float
    {
        return collect($netosLiquidados)->sum('netos');
    }
}
