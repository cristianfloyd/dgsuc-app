<?php

namespace App\Filament\Mapuche\Resources\NovedadesCargoImportResource\Pages;

use Filament\Actions;
use Illuminate\Support\Facades\Log;
use Filament\Resources\Pages\ListRecords;
use App\Services\NovedadesCargoImportTempService;
use App\Filament\Mapuche\Resources\NovedadesCargoImportResource;

class ListNovedadesCargoImports extends ListRecords
{
    protected static string $resource = NovedadesCargoImportResource::class;

    public $service;

    public function boot()
    {
        $this->service = new NovedadesCargoImportTempService();
        Log::info("ListNovedadesCargoImports::boot()");
    }

    public function mount(): void
    {

        $this->service->dropTempTable();
        $this->service->createTempTable();
    }
    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
