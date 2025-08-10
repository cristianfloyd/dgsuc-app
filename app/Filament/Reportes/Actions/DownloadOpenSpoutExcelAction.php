<?php

namespace App\Filament\Reportes\Actions;

use App\Exports\OpenSpoutReportExport;
use Filament\Actions\Action;

class DownloadOpenSpoutExcelAction extends Action
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Excel (Optimizado)')
            ->icon('heroicon-o-arrow-down-tray')
            ->color('warning')
            ->tooltip('Optimizado para volúmenes masivos de datos (más de 150.000 registros). Este proceso puede tardar unos minutos.')
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
                    'impp_conce',
                ]);

                return (new OpenSpoutReportExport($query))
                    ->download('reporte_concepto_listado_openspout.xlsx');
            })
            ->requiresConfirmation()
            ->modalHeading('¿Desea descargar el reporte con OpenSpout?')
            ->modalDescription('Se generará un archivo Excel optimizado para volúmenes masivos de datos (más de 150.000 registros). Este proceso puede tardar unos minutos pero utiliza menos memoria.')
            ->modalSubmitActionLabel('Descargar con OpenSpout');
    }

    public static function make(?string $name = null): static
    {
        $name ??= 'downloadOpenSpout';
        return parent::make($name);
    }
}
