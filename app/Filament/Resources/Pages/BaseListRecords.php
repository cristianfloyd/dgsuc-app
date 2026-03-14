<?php

namespace App\Filament\Resources\Pages;

use App\Services\TableManager\TableInitializationManager;
use Exception;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Log;

abstract class BaseListRecords extends ListRecords
{
    public function booted(): void
    {
        $this->initializeTableIfNeeded();
        // Log::info("BaseListRecords::booted desde {$this->getTableServiceClass()}");
    }

    protected function initializeTableIfNeeded(): void
    {
        $tableInitializationManager = resolve(TableInitializationManager::class);
        $service = resolve($this->getTableServiceClass());

        try {
            if (!$tableInitializationManager->isTableInitialized($service)) {
                $tableInitializationManager->initializeTable($service);
            }
        } catch (Exception $e) {
            report($e);
            // Opcional: Manejar el error de inicialización aquí
        }
    }

    abstract protected function getTableServiceClass(): string;
}
