<?php

namespace App\Filament\Mapuche\Resources\NovedadesCargoImports\Pages;

use App\Filament\Mapuche\Resources\NovedadesCargoImports\NovedadesCargoImports\NovedadesCargoImportResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditNovedadesCargoImport extends EditRecord
{
    protected static string $resource = NovedadesCargoImportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
