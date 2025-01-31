<?php

namespace App\Filament\Resources\ReporteConceptoListadoResource\Pages;

use Filament\Actions;
use App\Exports\ReportExport;
use App\Traits\ConceptoListadoTabs;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use App\Services\ConceptosSindicatosService;
use Maatwebsite\Excel\Facades\Excel as ExcelFacade;
use App\Filament\Reportes\Resources\ReporteConceptoListadoResource;

class ListReporteConceptoListados extends ListRecords
{
    use ConceptoListadoTabs;
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
                        'nro_liqui',
                        'desc_liqui',
                        'apellido',
                        'nombre',
                        'nro_legaj',
                        'nro_cargo',
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
