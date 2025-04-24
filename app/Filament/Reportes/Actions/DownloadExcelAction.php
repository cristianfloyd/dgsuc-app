<?php

namespace App\Filament\Reportes\Actions;

use Filament\Actions\Action;
use App\Exports\ReportExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Database\Eloquent\Builder;

class DownloadExcelAction extends Action
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Excel')
            ->icon('heroicon-o-document-arrow-down')
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

                return Excel::download(new ReportExport($query), 'reporte_concepto_listado.xlsx');
            })
            ->requiresConfirmation()
            ->modalHeading('¿Desea descargar el reporte?')
            ->modalDescription('Se generará un archivo Excel con los datos filtrados.')
            ->modalSubmitActionLabel('Descargar');
    }

    public static function make(?string $name = null): static
    {
        $name ??= 'download';
        return parent::make($name);
    }
}