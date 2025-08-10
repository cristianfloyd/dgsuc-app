<?php

namespace App\Filament\Bloqueos\Resources\BloqueosResource\Pages;

use App\Filament\Bloqueos\Resources\BloqueosResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

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
