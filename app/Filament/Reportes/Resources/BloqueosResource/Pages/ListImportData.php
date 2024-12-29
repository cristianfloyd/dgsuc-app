<?php

namespace App\Filament\Reportes\Resources\Bloqueos\Pages;

use Filament\Actions;
use App\Services\ImportDataTableService;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Reportes\Resources\BloqueosResource;

class ListImportData extends ListRecords
{
    protected static string $resource = BloqueosResource::class;

    public function mount(): void
    {
        app(ImportDataTableService::class)->ensureTableExists();
        parent::mount();
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Importar datos'),
        ];
    }
}
