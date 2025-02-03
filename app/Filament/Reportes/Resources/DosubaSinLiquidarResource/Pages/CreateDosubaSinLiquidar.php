<?php

namespace App\Filament\Reportes\Resources\DosubaSinLiquidarResource\Pages;

use App\Models\Mapuche\Dh22;
use App\Models\Mapuche\Dh21h;
use Illuminate\Support\Facades\Log;
use Filament\Resources\Pages\CreateRecord;
use App\Services\Mapuche\DosubaReportService;
use App\Services\Mapuche\PeriodoFiscalService;
use App\Models\Reportes\DosubaSinLiquidarModel;
use App\Filament\Reportes\Resources\DosubaSinLiquidarResource;

class CreateDosubaSinLiquidar extends CreateRecord
{
    public ?array $data = [
        'liquidacion_base' => null,
    ];
    protected static string $resource = DosubaSinLiquidarResource::class;

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

        // Obtenemos el periodo fiscal de la liquidación base
        $periodoFiscalService = app(PeriodoFiscalService::class);
        $periodoFiscal = $periodoFiscalService->getPeriodoFiscalFromId($liquidacionBase);

        // Utilizamos el servicio existente
        $dosubaService = app(DosubaReportService::class);
        $legajosSinLiquidar = $dosubaService->getDosubaReport(
            $periodoFiscal['year'],
            $periodoFiscal['month'],
        );

        // Guardamos en la tabla temporal
        DosubaSinLiquidarModel::setReportData($legajosSinLiquidar);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
