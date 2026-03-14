<?php

namespace App\Filament\Mapuche\Resources\AsignacionPresupuestarias\Pages;

use App\Filament\Mapuche\Resources\AsignacionPresupuestarias\AsignacionPresupuestarias\AsignacionPresupuestariaResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAsignacionPresupuestarias extends ListRecords
{
    protected static string $resource = AsignacionPresupuestariaResource::class;

    #[\Override]
    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
