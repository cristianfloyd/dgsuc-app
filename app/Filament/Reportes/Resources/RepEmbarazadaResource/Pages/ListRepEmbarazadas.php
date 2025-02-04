<?php

namespace App\Filament\Reportes\Resources\RepEmbarazadaResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Reportes\Resources\RepEmbarazadaResource;

class ListRepEmbarazadas extends ListRecords
{
    protected static string $resource = RepEmbarazadaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
        ];
    }
}
