<?php

namespace App\Filament\Bloqueos\Resources\Bloqueos\Pages;

use Filament\Actions\ViewAction;
use App\Filament\Bloqueos\Resources\Bloqueos\Bloqueos\BloqueosResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewBloqueo extends ViewRecord
{
    protected static string $resource = BloqueosResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
        ];
    }
}
