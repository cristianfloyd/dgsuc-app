<?php

namespace App\Filament\Resources\MapucheGrupoResource\Pages;

use Filament\Actions\DeleteAction;
use App\Filament\Resources\MapucheGrupoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMapucheGrupo extends EditRecord
{
    protected static string $resource = MapucheGrupoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
