<?php

namespace App\Filament\Resources\Reportes\RepOrdenPagoModelResource\Pages;

use App\Filament\Resources\Reportes\RepOrdenPagoModelResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRepOrdenPagoModels extends ListRecords
{
    protected static string $resource = RepOrdenPagoModelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
