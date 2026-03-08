<?php

namespace App\Filament\Afip\Resources\ControlDiferencias\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Afip\Resources\ControlDiferencias\ControlDiferenciasResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListControlDiferencias extends ListRecords
{
    protected static string $resource = ControlDiferenciasResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
