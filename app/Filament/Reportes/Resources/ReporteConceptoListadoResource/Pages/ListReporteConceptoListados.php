<?php

namespace App\Filament\Reportes\Resources\ReporteConceptoListadoResource\Pages;

use Filament\Actions;
use App\Models\Mapuche\Dh22;
use Maatwebsite\Excel\Excel;
use App\Exports\ReportExport;
use App\Services\Dh12Service;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\ConceptoListadoQueryService;
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
                        'desc_liqui',
                        'desc_appat',
                        'desc_nombr',
                        'nro_legaj',
                        'secuencia',
                        'codc_uacad',
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
        ];
    }
}
