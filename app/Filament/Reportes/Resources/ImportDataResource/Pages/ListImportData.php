<?php

namespace App\Filament\Reportes\Resources\ImportDataResource\Pages;

use App\Filament\Reportes\Resources\ImportDataResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListImportData extends ListRecords
{
    protected static string $resource = ImportDataResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
