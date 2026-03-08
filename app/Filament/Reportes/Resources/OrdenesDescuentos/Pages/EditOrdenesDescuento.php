<?php

namespace App\Filament\Reportes\Resources\OrdenesDescuentos\Pages;

use Filament\Actions\DeleteAction;
use App\Filament\Reportes\Resources\OrdenesDescuentos\OrdenesDescuentoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditOrdenesDescuento extends EditRecord
{
    protected static string $resource = OrdenesDescuentoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
