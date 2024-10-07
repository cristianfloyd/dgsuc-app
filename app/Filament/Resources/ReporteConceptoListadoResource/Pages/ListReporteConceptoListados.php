<?php

namespace App\Filament\Resources\ReporteConceptoListadoResource\Pages;

use Filament\Actions;
use App\Models\Mapuche\Dh22;
use Maatwebsite\Excel\Excel;
use App\Exports\ReportExport;
use App\Services\Dh12Service;
use App\Services\ConceptoListadoService;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Facades\Excel as ExcelFacade;
use App\Filament\Resources\ReporteConceptoListadoResource;

class ListReporteConceptoListados extends ListRecords
{
    protected static string $resource = ReporteConceptoListadoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\Action::make('csv')->label('CSV')
            //         ->icon('heroicon-o-document-arrow-down')
            //         ->button()
            //     ->action(function () {
            //                 $query = $this->getFilteredTableQuery();
            //                 return (new ReportExport($query))->download('invoices.csv', Excel::CSV, ['Content-Type' => 'text/csv']);
            //             })
            //             ->requiresConfirmation()
            //             ->modalHeading('¿Desea descargar el reporte?')
            //             ->modalDescription('Se generará un archivo Excel con los datos filtrados.')
            //             ->modalSubmitActionLabel('Descargar'),
            Actions\Action::make('download')->label('Excel')
                ->icon('heroicon-o-document-arrow-down')
                ->button()
                ->action(function ($data)  {
                    //implementar un servicio que pase la query a excel a traves de laravel-excel
                        $query = $this->getFilteredTableQuery();
                        // comprobar que el query no este vacion
                        if($query->count() == 0){
                            return redirect()->route('filament.resources.reporte-concepto-listado.index')
                                ->with('error', 'No se encontraron registros con los filtros seleccionados.');
                        }

                        return ExcelFacade::download(new ReportExport($query), 'reporte_concepto_listado.xlsx');
                    })
                    ->requiresConfirmation()
                    ->modalHeading('¿Desea descargar el reporte?')
                    ->modalDescription('Se generará un archivo Excel con los datos filtrados.')
                    ->modalSubmitActionLabel('Descargar'),
            Actions\SelectAction::make('periodo_fiscal')->label('Periodo')
                ->options(Dh22::getPeriodosFiscales()),
            Actions\SelectAction::make('codn_conce')
                ->label('concepto')
                ->options(function () {
                    return Dh12Service::getConceptosParaSelect();
                })
                ->action(function($record, $data){
                    $this->tableFilters['codn_conce'] = $data;
                    $this->refreshTable();
                })
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $service = app(ConceptoListadoService::class);
        return $service->getQueryForConcepto(request()->input('tableFilters.codn_conce', 225));
    }

}
