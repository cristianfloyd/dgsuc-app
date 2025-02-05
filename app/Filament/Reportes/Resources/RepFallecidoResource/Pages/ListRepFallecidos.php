<?php

namespace App\Filament\Reportes\Resources\RepFallecidoResource\Pages;

use App\Filament\Reportes\Resources\RepFallecidoResource;
use App\Services\TableManager\TableInitializationManager;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRepFallecidos extends ListRecords
{
    protected static string $resource = RepFallecidoResource::class;

    public function mount(): void
    {
        $manager = app(TableInitializationManager::class);
        $modelClass = static::$resource::getModel();
        $service = app($modelClass::getTableServiceClass());

        try {
            if (!$manager->isTableInitialized($service)) {
                $manager->initializeTable($service);
            }
        } catch (\Exception $e) {
            report($e);
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            //Actions\CreateAction::make(),
        ];
    }
}
