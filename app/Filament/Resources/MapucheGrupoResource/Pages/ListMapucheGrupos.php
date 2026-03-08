<?php

namespace App\Filament\Resources\MapucheGrupoResource\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\MapucheGrupoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMapucheGrupos extends ListRecords
{
    protected static string $resource = MapucheGrupoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
