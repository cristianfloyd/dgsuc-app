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
                    // comprobar que el query no este vacio
                    // if ($query->count() == 0) {
                    //     return redirect()->route('filament.resources.reporte-concepto-listado.index')
                    //         ->with('error', 'No se encontraron registros con los filtros seleccionados.');
                    // }
                    $query = $query->select([
                        'dh21.nro_legaj',
                        'lc.codc_uacad',
                        DB::raw("CONCAT(dh22.per_liano, LPAD(CAST(dh22.per_limes AS TEXT), 2, '0')) as periodo_fiscal"),
                        'dh22.desc_liqui',
                        DB::raw("concat(dh01.nro_cuil1, dh01.nro_cuil, dh01.nro_cuil2) AS cuil"),
                        'dh01.desc_appat',
                        'dh01.desc_nombr',
                        'coddependesemp',
                        'lc.nro_cargo as secuencia',
                        'dh21.codn_conce',
                        'dh21.impp_conce'
                    ]);
                    // dd($query->get()->toArray());
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
