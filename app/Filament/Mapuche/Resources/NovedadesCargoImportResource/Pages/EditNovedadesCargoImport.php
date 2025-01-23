<?php

namespace App\Filament\Mapuche\Resources\NovedadesCargoImportResource\Pages;

use App\Filament\Mapuche\Resources\NovedadesCargoImportResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditNovedadesCargoImport extends EditRecord
{
    protected static string $resource = NovedadesCargoImportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
