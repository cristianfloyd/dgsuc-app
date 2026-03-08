<?php

namespace App\Filament\Reportes\Resources\ComprobanteNominaModels\Pages;

use Filament\Actions\DeleteAction;
use App\Filament\Reportes\Resources\ComprobanteNominaModels\ComprobanteNominaModelResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditComprobanteNominaModel extends EditRecord
{
    protected static string $resource = ComprobanteNominaModelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
