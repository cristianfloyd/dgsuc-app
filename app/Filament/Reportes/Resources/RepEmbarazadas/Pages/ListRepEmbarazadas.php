<?php

namespace App\Filament\Reportes\Resources\RepEmbarazadas\Pages;

use App\Filament\Reportes\Resources\RepEmbarazadas\RepEmbarazadas\RepEmbarazadaResource;
use App\Services\RepEmbarazadaService;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRepEmbarazadas extends ListRecords
{
    protected static string $resource = RepEmbarazadaResource::class;

    #[\Override]
    public function mount(): void
    {
        resolve(RepEmbarazadaService::class)->ensureTableExists();
        parent::mount();
    }

    #[\Override]
    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
        ];
    }
}
