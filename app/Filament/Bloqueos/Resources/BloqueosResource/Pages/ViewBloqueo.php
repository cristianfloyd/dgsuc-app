<?php

namespace App\Filament\Bloqueos\Resources\BloqueosResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use App\Filament\Bloqueos\Resources\BloqueosResource;

class ViewBloqueo extends ViewRecord
{
    protected static string $resource = BloqueosResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
        ];
    }
}
