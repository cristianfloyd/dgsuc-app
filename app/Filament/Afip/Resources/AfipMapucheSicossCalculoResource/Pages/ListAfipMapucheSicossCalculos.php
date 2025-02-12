<?php

namespace App\Filament\Afip\Resources\AfipMapucheSicossCalculoResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use App\Services\AfipMapucheSicossCalculoTableService;
use App\Services\TableManager\TableInitializationManager;
use App\Filament\Afip\Resources\AfipMapucheSicossCalculoResource;

class ListAfipMapucheSicossCalculos extends ListRecords
{
    protected static string $resource = AfipMapucheSicossCalculoResource::class;

    public function mount(): void
    {
        $manager = app(TableInitializationManager::class);
        $service = app(AfipMapucheSicossCalculoTableService::class);

        try {
            if (!$manager->isTableInitialized($service)) {
                $manager->initializeTable($service);
            }
        } catch (\Exception $e) {
            report($e);
        }
    }
}
