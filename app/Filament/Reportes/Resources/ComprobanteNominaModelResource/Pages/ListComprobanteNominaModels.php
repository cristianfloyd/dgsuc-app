<?php

namespace App\Filament\Reportes\Resources\ComprobanteNominaModelResource\Pages;

use App\Filament\Reportes\Resources\ComprobanteNominaModelResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListComprobanteNominaModels extends ListRecords
{
    protected static string $resource = ComprobanteNominaModelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
