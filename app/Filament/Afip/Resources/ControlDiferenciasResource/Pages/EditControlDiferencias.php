<?php

namespace App\Filament\Afip\Resources\ControlDiferenciasResource\Pages;

use App\Filament\Afip\Resources\ControlDiferenciasResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditControlDiferencias extends EditRecord
{
    protected static string $resource = ControlDiferenciasResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
