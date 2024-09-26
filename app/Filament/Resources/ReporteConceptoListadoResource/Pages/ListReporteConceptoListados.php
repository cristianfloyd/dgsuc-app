<?php

namespace App\Filament\Resources\ReporteConceptoListadoResource\Pages;

use Filament\Actions;
use Maatwebsite\Excel\Excel;
use App\Exports\ReportExport;
use Filament\Actions\ExportAction;
use Filament\Resources\Components\Tab;
use App\Services\ConceptoListadoService;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Filament\Actions\Exports\Enums\ExportFormat;
use Maatwebsite\Excel\Facades\Excel as ExcelFacade;
use App\Filament\Exports\Reportes\ConceptoListadoExporter;
use App\Filament\Resources\ReporteConceptoListadoResource;
use App\Services\Dh12Service;

class ListReporteConceptoListados extends ListRecords
{
    protected static string $resource = ReporteConceptoListadoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //Actions\CreateAction::make(),
            Actions\Action::make('csv')->label('CSV')
                    ->icon('heroicon-o-document-arrow-down')
                    ->button()
                ->action(function (Builder $query) {
                        //implementar un servicio que pase la query a excel a traves de laravel-excel
                        //https://github.com/Maatwebsite/Laravel-Excel
                            $query = static::getEloquentQuery();

                            return (new ReportExport($query))->download('invoices.csv', Excel::CSV, ['Content-Type' => 'text/csv']);

                        })
                        ->requiresConfirmation()
                        ->modalHeading('¿Desea descargar el reporte?')
                        ->modalDescription('Se generará un archivo Excel con los datos filtrados.')
                        ->modalSubmitActionLabel('Descargar'),
            Actions\Action::make('download')->label('Excel')
                ->icon('heroicon-o-document-arrow-down')
                ->button()
                ->action(function ($data)  {
                    //implementar un servicio que pase la query a excel a traves de laravel-excel
                    //https://github.com/Maatwebsite/Laravel-Excel
                        $query = static::getEloquentQuery();
                        return ExcelFacade::download(new ReportExport($query), 'reporte_concepto_listado.xlsx');
                    })
                    ->requiresConfirmation()
                    ->modalHeading('¿Desea descargar el reporte?')
                    ->modalDescription('Se generará un archivo Excel con los datos filtrados.')
                    ->modalSubmitActionLabel('Descargar'),
            Actions\SelectAction::make('concepto')->label('concepto')
                ->options(function () {
                    return Dh12Service::getConceptosParaSelect();
                })
        ];
    }
}
