<?php

namespace App\Filament\Reportes\Resources\ComprobanteNominaModelResource\Pages;

use App\Filament\Reportes\Resources\ComprobanteNominaModelResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditComprobanteNominaModel extends EditRecord
{
    protected static string $resource = ComprobanteNominaModelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
