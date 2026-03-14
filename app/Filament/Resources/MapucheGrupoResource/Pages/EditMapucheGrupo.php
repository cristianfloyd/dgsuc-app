<?php

namespace App\Filament\Resources\MapucheGrupoResource\Pages;

use App\Filament\Resources\MapucheGrupoResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditMapucheGrupo extends EditRecord
{
    protected static string $resource = MapucheGrupoResource::class;

    #[\Override]
    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
