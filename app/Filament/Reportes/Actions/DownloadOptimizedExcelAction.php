<?php

namespace App\Filament\Reportes\Actions;

use App\Exports\LazyReportExport;
use Filament\Actions\Action;
use Illuminate\Database\Eloquent\Builder;

class DownloadOptimizedExcelAction extends Action
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Excel')
            ->icon('heroicon-o-arrow-down-circle')
            ->tooltip('Para volúmenes de pocos registros. Hasta 2000 registros.')
            ->color('success')
            ->button()
            ->action(function () {
                $livewire = $this->getLivewire();
                $query = $livewire->getFilteredSortedTableQuery();

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
            ->modalSubmitActionLabel('Descargar Optimizado');
    }

    public static function make(?string $name = null): static
    {
        $name ??= 'downloadOptimized';
        return parent::make($name);
    }
}
