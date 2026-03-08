<?php

namespace App\Filament\Reportes\Resources\ReporteConceptoListados\Pages;

use App\Filament\Reportes\Actions\DownloadExcelAction;
use App\Filament\Reportes\Actions\DownloadOpenSpoutExcelAction;
use App\Filament\Reportes\Actions\DownloadOptimizedExcelAction;
use App\Filament\Reportes\Resources\ReporteConceptoListados\ReporteConceptoListadoResource;
use App\Traits\ConceptoListadoTabs;
use Filament\Resources\Pages\ListRecords;

class ListReporteConceptoListados extends ListRecords
{
    use ConceptoListadoTabs;

    protected static string $resource = ReporteConceptoListadoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // DownloadExcelAction::make(),
            DownloadOptimizedExcelAction::make(),
            DownloadOpenSpoutExcelAction::make(),
        ];
    }
}
