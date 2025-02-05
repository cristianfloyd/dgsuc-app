<?php

namespace App\Filament\Reportes\Resources\RepEmbarazadaResource\Pages;

use Filament\Actions;
use App\Services\RepEmbarazadaService;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Reportes\Resources\RepEmbarazadaResource;

class ListRepEmbarazadas extends ListRecords
{
    protected static string $resource = RepEmbarazadaResource::class;

    public function mount(): void
    {
        app(RepEmbarazadaService::class)->ensureTableExists();
        parent::mount();
    }

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
        ];
    }
}
