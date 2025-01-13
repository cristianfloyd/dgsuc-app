<?php

namespace App\Filament\Reportes\Resources\BloqueosResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Reportes\Resources\BloqueosResource;

class EditImportData extends EditRecord
{
    protected static string $resource = BloqueosResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
