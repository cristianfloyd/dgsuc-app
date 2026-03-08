<?php

namespace App\Filament\Mapuche\Resources\AsignacionPresupuestarias\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Mapuche\Resources\AsignacionPresupuestarias\AsignacionPresupuestarias\AsignacionPresupuestariaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAsignacionPresupuestarias extends ListRecords
{
    protected static string $resource = AsignacionPresupuestariaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
