<?php

namespace App\Filament\Embargos\Resources\EmbargoReports\Pages;

use Filament\Actions\Action;
use App\Filament\Embargos\Resources\EmbargoReports\EmbargoReportResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEmbargos extends ListRecords
{
    protected static string $resource = EmbargoReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('generarReporte')
                ->label('Generar Reporte')
                ->icon('heroicon-o-document-currency-dollar')
                ->url('/embargos/embargo-reports/reporte')
                ->color('success'),
        ];
    }
}
