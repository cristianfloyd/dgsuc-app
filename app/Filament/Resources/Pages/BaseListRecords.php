<?php

namespace App\Filament\Resources\Pages;

use Illuminate\Support\Facades\Log;
use Filament\Resources\Pages\ListRecords;
use App\Services\TableManager\TableInitializationManager;

abstract class BaseListRecords extends ListRecords
{
    public function booted(): void
    {
        $this->initializeTableIfNeeded();
        Log::info("BaseListRecords::booted desde {$this->getTableServiceClass()}");
    }

    protected function initializeTableIfNeeded(): void
    {
        $manager = app(TableInitializationManager::class);
        $service = app($this->getTableServiceClass());

        try {
            if (!$manager->isTableInitialized($service)) {
                $manager->initializeTable($service);
            }
        } catch (\Exception $e) {
            report($e);
            // Opcional: Manejar el error de inicialización aquí
        }
    }

    abstract protected function getTableServiceClass(): string;
}
