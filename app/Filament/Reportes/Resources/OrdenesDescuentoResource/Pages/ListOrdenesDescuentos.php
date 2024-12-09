<?php

namespace App\Filament\Reportes\Resources\OrdenesDescuentoResource\Pages;

use App\Filament\Reportes\Resources\OrdenesDescuentoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListOrdenesDescuentos extends ListRecords
{
    protected static string $resource = OrdenesDescuentoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
