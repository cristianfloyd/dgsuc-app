<?php

namespace App\Filament\Reportes\Resources\RepEmbarazadaResource\Pages;

use App\Filament\Reportes\Resources\RepEmbarazadaResource;
use App\Services\RepEmbarazadaService;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

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
