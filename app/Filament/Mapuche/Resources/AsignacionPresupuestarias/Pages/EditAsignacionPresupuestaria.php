<?php

namespace App\Filament\Mapuche\Resources\AsignacionPresupuestarias\Pages;

use App\Filament\Mapuche\Resources\AsignacionPresupuestarias\AsignacionPresupuestarias\AsignacionPresupuestariaResource;
use Filament\Actions\DeleteAction;
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
