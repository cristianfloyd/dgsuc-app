<?php

namespace App\Filament\Reportes\Resources\ReporteConceptoListadoResource\Pages;

use Filament\Actions;
use App\Models\Mapuche\Dh22;
use Maatwebsite\Excel\Excel;
use App\Exports\ReportExport;
use App\Exports\LazyReportExport;
use Illuminate\Support\Facades\DB;
use App\Traits\ConceptoListadoTabs;
use Illuminate\Support\Facades\Log;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Facades\Excel as ExcelFacade;
use App\Filament\Reportes\Actions\DownloadExcelAction;
use App\Filament\Reportes\Actions\DownloadOpenSpoutExcelAction;
use App\Filament\Reportes\Actions\DownloadOptimizedExcelAction;
use App\Filament\Reportes\Resources\ReporteConceptoListadoResource;

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
