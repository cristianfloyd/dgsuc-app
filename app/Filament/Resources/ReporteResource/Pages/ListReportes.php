<?php

namespace App\Filament\Resources\ReporteResource\Pages;

use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\ReporteResource;

class ListReportes extends ListRecords
{
    protected static string $resource = ReporteResource::class;
    protected static string $view = 'filament.resources.reporte-resource.pages.list-reportes';


    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
            // ReporteResource::getActions(),
            Action::make('ordenPagoReporte')
                ->label('Orden de Pago')
                ->modalContent(fn () => view('modals.orden-pago-reporte', ['liquidacionId' => 1]))
                ->modalWidth('7xl'),
        ];
    }
}
