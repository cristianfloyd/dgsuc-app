<?php

namespace App\Filament\Reportes\Resources\OrdenesDescuentoResource\Pages;

use App\Filament\Reportes\Resources\OrdenesDescuentoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditOrdenesDescuento extends EditRecord
{
    protected static string $resource = OrdenesDescuentoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
