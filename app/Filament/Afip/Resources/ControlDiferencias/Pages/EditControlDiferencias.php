<?php

namespace App\Filament\Afip\Resources\ControlDiferencias\Pages;

use App\Filament\Afip\Resources\ControlDiferencias\ControlDiferencias\ControlDiferenciasResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditControlDiferencias extends EditRecord
{
    protected static string $resource = ControlDiferenciasResource::class;

    #[\Override]
    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
