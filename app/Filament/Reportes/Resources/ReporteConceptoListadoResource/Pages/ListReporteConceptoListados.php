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
use App\Filament\Reportes\Resources\ReporteConceptoListadoResource;

class ListReporteConceptoListados extends ListRecords
{
    use ConceptoListadoTabs;
    protected static string $resource = ReporteConceptoListadoResource::class;


    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('download')
                ->label('Excel')
                ->icon('heroicon-o-document-arrow-down')
                ->button()
                ->action(function ($livewire) {
                    //implementar un servicio que pase la query a excel a traves de laravel-excel
                    $query = $this->getFilteredSortedTableQuery();
                    // $query2 = $livewire->getTableQuery();


                    // comprobar que el query no este vacio
                    if ($query->count() == 0) {
                        return redirect()->route('filament.reportes.resources.reporte-concepto-listado.index')
                            ->with('error', 'No se encontraron registros con los filtros seleccionados.');
                    }
                    $query = $query->select([
                        'nro_liqui',
                        'desc_liqui',
                        'apellido',
                        'nombre',
                        'cuil',
                        'nro_legaj',
                        'nro_cargo',
                        'codc_uacad',
                        'codn_conce',
                        'impp_conce'
                    ])
                    ;

                    return ExcelFacade::download(new ReportExport($query), 'reporte_concepto_listado.xlsx');
                })
                ->requiresConfirmation()
                ->modalHeading('¿Desea descargar el reporte?')
                ->modalDescription('Se generará un archivo Excel con los datos filtrados.')
                ->modalSubmitActionLabel('Descargar'),
            // Nueva acción optimizada para grandes volúmenes
            Actions\Action::make('downloadOptimized')
                ->label('Excel (Optimizado)')
                ->icon('heroicon-o-arrow-down-circle')
                ->color('success')
                ->button()
                ->action(function ($livewire) {
                    $query = $this->getFilteredSortedTableQuery();

                    if ($query->count() == 0) {
                        return redirect()->route('filament.reportes.resources.reporte-concepto-listado.index')
                            ->with('error', 'No se encontraron registros con los filtros seleccionados.');
                    }

                    $query = $query->select([
                        'nro_liqui',
                        'desc_liqui',
                        'apellido',
                        'nombre',
                        'cuil',
                        'nro_legaj',
                        'nro_cargo',
                        'codc_uacad',
                        'codn_conce',
                        'impp_conce'
                    ]);

                    return (new LazyReportExport($query))
                        ->download('reporte_concepto_listado_optimizado.xlsx');
                })
                ->requiresConfirmation()
                ->modalHeading('¿Desea descargar el reporte optimizado?')
                ->modalDescription('Se generará un archivo Excel optimizado para grandes volúmenes de datos. Este proceso puede tardar unos minutos.')
                ->modalSubmitActionLabel('Descargar Optimizado'),
        ];
    }
}
