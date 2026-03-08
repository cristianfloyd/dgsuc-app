<?php

namespace App\Filament\Afip\Resources\ControlDiferencias\Pages;

use Filament\Actions\DeleteAction;
use App\Filament\Afip\Resources\ControlDiferencias\ControlDiferenciasResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditControlDiferencias extends EditRecord
{
    protected static string $resource = ControlDiferenciasResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
