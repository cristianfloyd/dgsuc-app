<?php

namespace App\Filament\Mapuche\Resources\AsignacionPresupuestariaResource\Pages;

use App\Filament\Mapuche\Resources\AsignacionPresupuestariaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAsignacionPresupuestaria extends EditRecord
{
    protected static string $resource = AsignacionPresupuestariaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
