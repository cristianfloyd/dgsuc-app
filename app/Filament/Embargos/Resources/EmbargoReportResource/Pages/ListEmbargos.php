<?php

namespace App\Filament\Embargos\Resources\EmbargoReportResource\Pages;

use App\Filament\Embargos\Resources\EmbargoReportResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEmbargos extends ListRecords
{
    protected static string $resource = EmbargoReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('generarReporte')
                ->label('Generar Reporte')
                ->icon('heroicon-o-document-currency-dollar')
                ->url('/embargos/embargo-reports/reporte')
                ->color('success'),
        ];
    }
}
