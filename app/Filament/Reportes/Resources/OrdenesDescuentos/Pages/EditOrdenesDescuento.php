<?php

namespace App\Filament\Reportes\Resources\OrdenesDescuentos\Pages;

use App\Filament\Reportes\Resources\OrdenesDescuentos\OrdenesDescuentos\OrdenesDescuentoResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditOrdenesDescuento extends EditRecord
{
    protected static string $resource = OrdenesDescuentoResource::class;

    #[\Override]
    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
