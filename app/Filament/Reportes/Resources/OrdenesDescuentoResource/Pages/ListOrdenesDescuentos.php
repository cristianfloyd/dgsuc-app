<?php

namespace App\Filament\Reportes\Resources\OrdenesDescuentoResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Reportes\Resources\OrdenesDescuentoResource;

class ListOrdenesDescuentos extends ListRecords
{
    protected static string $resource = OrdenesDescuentoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            
        ];
    }
}
