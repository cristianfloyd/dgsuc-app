<?php

namespace App\Filament\Reportes\Resources\ComprobanteNominaModelResource\Pages;

use App\Filament\Reportes\Resources\ComprobanteNominaModelResource\ComprobanteNominaModels\ComprobanteNominaModelResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditComprobanteNominaModel extends EditRecord
{
    protected static string $resource = ComprobanteNominaModelResource::class;

    #[\Override]
    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
