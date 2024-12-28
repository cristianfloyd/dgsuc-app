<?php

namespace App\Filament\Reportes\Resources\ImportDataResource\Pages;

use Filament\Actions;
use App\Services\ImportDataTableService;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Reportes\Resources\ImportDataResource;

class ListImportData extends ListRecords
{
    protected static string $resource = ImportDataResource::class;

    public function mount(): void
    {
        app(ImportDataTableService::class)->ensureTableExists();
        parent::mount();
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
