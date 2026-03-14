<?php

namespace App\Filament\Afip\Resources\ControlDiferencias\Pages;

use App\Filament\Afip\Resources\ControlDiferencias\ControlDiferencias\ControlDiferenciasResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListControlDiferencias extends ListRecords
{
    protected static string $resource = ControlDiferenciasResource::class;

    #[\Override]
    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
