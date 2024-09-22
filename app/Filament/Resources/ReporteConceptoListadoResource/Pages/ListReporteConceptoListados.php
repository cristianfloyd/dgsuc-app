<?php

namespace App\Filament\Resources\ReporteConceptoListadoResource\Pages;

use Filament\Actions;
use App\Exports\ReportExport;
use Filament\Actions\ExportAction;
use Maatwebsite\Excel\Facades\Excel;
use Filament\Resources\Components\Tab;
use App\Services\ConceptoListadoService;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Filament\Actions\Exports\Enums\ExportFormat;
use App\Filament\Exports\Reportes\ConceptoListadoExporter;
use App\Filament\Resources\ReporteConceptoListadoResource;

class ListReporteConceptoListados extends ListRecords
{
    protected static string $resource = ReporteConceptoListadoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //Actions\CreateAction::make(),
            ExportAction::make('excel')->label('Excel')
                ->exporter(ConceptoListadoExporter::class)
                ->formats([
                    ExportFormat::Csv,
                ]),
            Actions\Action::make('download')->label('Excel')
                ->icon('heroicon-o-document-arrow-down')
                ->button()
                ->action(function ($data)  {
                    //implementar un servicio que pase la query a excel a traves de laravel-excel
                    //https://github.com/Maatwebsite/Laravel-Excel
                    $query = static::getEloquentQuery();
                    return Excel::download(new ReportExport($query), 'reporte_concepto_listado.xlsx');
                    })
                    ->requiresConfirmation()
                    ->modalHeading('¿Desea descargar el reporte?')
                    ->modalDescription('Se generará un archivo Excel con los datos filtrados.')
                    ->modalSubmitActionLabel('Descargar'),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('Todos'),
            '225' => Tab::make('225')->query(function ($query) {
                return $query->where('codn_conce', 225);
            }),
            '258' => Tab::make('258')->query(function ($query) {
                return $query->where('codn_conce', 258);
            }),
            '266' => Tab::make('266')->query(function ($query) {
                return $query->where('codn_conce', 266);
            })
        ];
    }

    public function getEloquentQuery(): Builder
    {
        $service = app(ConceptoListadoService::class);
        return $service->getQueryForConcepto(request()->input('tableFilters.codn_conce', 225));
    }
}
