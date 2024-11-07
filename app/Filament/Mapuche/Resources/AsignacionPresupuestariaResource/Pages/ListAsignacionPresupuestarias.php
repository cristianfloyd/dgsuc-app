<?php

namespace App\Filament\Mapuche\Resources\AsignacionPresupuestariaResource\Pages;

use App\Filament\Mapuche\Resources\AsignacionPresupuestariaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAsignacionPresupuestarias extends ListRecords
{
    protected static string $resource = AsignacionPresupuestariaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
