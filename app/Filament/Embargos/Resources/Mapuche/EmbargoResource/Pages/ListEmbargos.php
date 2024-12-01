<?php

namespace App\Filament\Embargos\Resources\Mapuche\EmbargoResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Embargos\Resources\Mapuche\EmbargoResource;

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
        ];
    }
}
