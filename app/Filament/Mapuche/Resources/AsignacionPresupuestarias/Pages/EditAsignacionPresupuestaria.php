<?php

namespace App\Filament\Mapuche\Resources\AsignacionPresupuestarias\Pages;

use Filament\Actions\DeleteAction;
use App\Filament\Mapuche\Resources\AsignacionPresupuestarias\AsignacionPresupuestariaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAsignacionPresupuestaria extends EditRecord
{
    protected static string $resource = AsignacionPresupuestariaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
