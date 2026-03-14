<?php

namespace App\Filament\Reportes\Resources\RepFallecidos\Pages;

use App\Filament\Reportes\Resources\RepFallecidos\RepFallecidos\RepFallecidoResource;
use App\Services\TableManager\TableInitializationManager;
use Exception;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRepFallecidos extends ListRecords
{
    protected static string $resource = RepFallecidoResource::class;

    #[\Override]
    public function mount(): void
    {
        $tableInitializationManager = resolve(TableInitializationManager::class);
        $modelClass = static::$resource::getModel();
        $service = resolve($modelClass::getTableServiceClass());

        try {
            if (!$tableInitializationManager->isTableInitialized($service)) {
                $tableInitializationManager->initializeTable($service);
            }
        } catch (Exception $e) {
            report($e);
        }
    }

    #[\Override]
    protected function getHeaderActions(): array
    {
        return [
            //Actions\CreateAction::make(),
        ];
    }
}
