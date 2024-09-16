<?php

namespace App\Filament\Resources\Dh21Resource\Pages;

use Livewire\Livewire;
use Filament\Actions\Action;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Filament\Resources\Dh21Resource;
use App\Filament\Widgets\IdLiquiSelector;
use Filament\Resources\Pages\ListRecords;
use App\Livewire\Reportes\OrdenPagoReporte;
use App\Filament\Resources\Dh21Resource\Widgets\Dh21Concepto101Total;

class ListDh21s extends ListRecords
{
    protected static string $resource = Dh21Resource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
            Action::make('conceptos-totales')
            ->label('Ver Conceptos Totales')
            ->url(static::getResource()::getUrl('conceptos-totales')),
            Action::make('abrirModal')
                ->label('Orden de Pago')
                ->icon('heroicon-o-plus')
                ->modalContent(function () {
                    return view('modals.orden-pago-reporte', ['liquidacionId' => 1]);
                })
                ->modalFooterActions([
                    Action::make('descargarPDF')
                    ->label('Descargar PDF')
                    ->action(fn() => static::descargarReportePDF(1))
                ])
                ->modalDescription('Orden de pago') // Contenido vacío
                ->modalWidth('7xl'), // Ancho del modal
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            //Dh21LegajoCounter::class,
            Dh21Concepto101Total::class,
            IdLiquiSelector::class,
        ];
    }

    protected static function descargarReportePDF($liquidacionId)
    {
        $component = Livewire::test(OrdenPagoReporte::class, ['liquidacionId' => $liquidacionId]);
        return $component->call('descargarPDF');
    }
}
