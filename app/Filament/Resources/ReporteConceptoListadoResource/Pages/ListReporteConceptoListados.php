<?php

namespace App\Filament\Resources\ReporteConceptoListadoResource\Pages;

use Filament\Actions;
use App\Models\Mapuche\Dh22;
use Maatwebsite\Excel\Excel;
use App\Exports\ReportExport;
use App\Services\Dh12Service;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\ConceptoListadoService;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Table\Concerns\HasRecords;
use Maatwebsite\Excel\Facades\Excel as ExcelFacade;
use App\Filament\Resources\ReporteConceptoListadoResource;

class ListReporteConceptoListados extends ListRecords
{
    protected static string $resource = ReporteConceptoListadoResource::class;


    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('download')->label('Excel')
                ->icon('heroicon-o-document-arrow-down')
                ->button()
                ->action(function ($data) {
                    //implementar un servicio que pase la query a excel a traves de laravel-excel
                    $query = $this->getFilteredSortedTableQuery();
                    // dd($query->toSql());
                    // comprobar que el query no este vacio
                    if ($query->count() == 0) {
                        return redirect()->route('filament.resources.reporte-concepto-listado.index')
                            ->with('error', 'No se encontraron registros con los filtros seleccionados.');
                    }
                    $query = $query->select([
                        'codc_uacad',
                        'periodo_fiscal',
                        'desc_liqui',
                        'nro_legaj',
                        'cuil',
                        'desc_appat',
                        'desc_nombr',
                        'coddependesemp',
                        'secuencia',
                        'codn_conce',
                        'impp_conce'
                    ])
                    // ->orderBy('codc_uacad')
                    // ->orderBy('nro_legaj')
                    ;
                    // dd($query->toSql());
                    return ExcelFacade::download(new ReportExport($query), 'reporte_concepto_listado.xlsx');
                })
                ->requiresConfirmation()
                ->modalHeading('¿Desea descargar el reporte?')
                ->modalDescription('Se generará un archivo Excel con los datos filtrados.')
                ->modalSubmitActionLabel('Descargar'),
        // Actions\SelectAction::make('periodo_fiscal')->label('Periodo')
        //     ->options(Dh22::getPeriodosFiscales()),
        // Actions\SelectAction::make('codn_conce')
        //     ->label('concepto')
        //     ->options(function () {
        //         return Dh12Service::getConceptosParaSelect();
        //     })
        //     ->action(function ($record, $data) {
        //         $this->tableFilters['codn_conce'] = $data;
        //         $this->refreshTable();
        //     })
        ];
    }
}
