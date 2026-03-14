<?php

namespace App\Filament\Reportes\Resources\DosubaSinLiquidars\Pages;

use App\Filament\Reportes\Resources\DosubaSinLiquidars\DosubaSinLiquidars\DosubaSinLiquidarResource;
use App\Models\Reportes\DosubaSinLiquidarModel;
use App\Services\Mapuche\DosubaReportService;
use App\Services\Mapuche\PeriodoFiscalService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Log;

class CreateDosubaSinLiquidar extends CreateRecord
{
    public ?array $data = [
        'liquidacion_base' => null,
    ];

    protected static string $resource = DosubaSinLiquidarResource::class;

    #[\Override]
    public function mount(): void
    {
        DosubaSinLiquidarModel::createTableIfNotExists();
        DosubaSinLiquidarModel::cleanOldRecords();
        Log::info('CreateDosubaSinLiquidar mount');
    }

    protected function beforeCreate(): void
    {
        // Lógica para generar el reporte basado en la liquidación seleccionada
        $formData = $this->form->getState();
        $liquidacionBase = $formData['liquidacion_base'];

        Log::info('liquidacion seleccionada:', ['liquidacion' => $liquidacionBase]);
        // Obtenemos el periodo fiscal de la liquidación base
        $periodoFiscalService = resolve(PeriodoFiscalService::class);
        $periodoFiscal = $periodoFiscalService->getPeriodoFiscalFromId($liquidacionBase);

        Log::info('periodo fiscal:', ['periodo' => $periodoFiscal]);
        // Utilizamos el servicio existente
        $dosubaReportService = resolve(DosubaReportService::class);
        $legajosSinLiquidar = $dosubaReportService->getDosubaReport(
            $periodoFiscal['year'],
            $periodoFiscal['month'],
        );

        // Guardamos en la tabla temporal
        DosubaSinLiquidarModel::setReportData($legajosSinLiquidar);
    }

    #[\Override]
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
