<?php

namespace App\Filament\Bloqueos\Resources\Bloqueos\Pages;

use App\Filament\Bloqueos\Resources\Bloqueos\Bloqueos\BloqueosResource;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\ViewRecord;

class ViewBloqueo extends ViewRecord
{
    protected static string $resource = BloqueosResource::class;

    #[\Override]
    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
        ];
    }
}
