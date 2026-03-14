<?php

namespace App\Filament\Embargos\Resources\EmbargoReports\Pages;

use App\Filament\Embargos\Resources\EmbargoReports\EmbargoReports\EmbargoReportResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;

class ListEmbargos extends ListRecords
{
    protected static string $resource = EmbargoReportResource::class;

    #[\Override]
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
