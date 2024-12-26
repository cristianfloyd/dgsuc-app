<?php

namespace App\Filament\Reportes\Resources\ImportDataResource\Pages;

use App\Filament\Reportes\Resources\ImportDataResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditImportData extends EditRecord
{
    protected static string $resource = ImportDataResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
