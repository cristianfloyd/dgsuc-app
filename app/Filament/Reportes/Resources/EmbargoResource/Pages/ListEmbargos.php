<?php

namespace App\Filament\Reportes\Resources\EmbargoResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Reportes\Resources\EmbargoResource;

class ListEmbargos extends ListRecords
{
    protected static string $resource = EmbargoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\Action::make('report')
            //     ->label('Reporte')
            //     ->url(fn (): string => $this->getResource()::getUrl('reporte-embargos'))
            //     ->icon('heroicon-o-document-check')
            Actions\Action::make('generarReporte')
            ->label('Generar Reporte')
            ->icon('heroicon-o-document-currency-dollar')
            ->url('/reportes/embargos/reporte')
            ->color('success')
        ];
    }
}
