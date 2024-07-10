<?php

namespace App\Filament\Resources\AfipMapucheMiSimplificacionResource\Pages;

use App\Filament\Resources\AfipMapucheMiSimplificacionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAfipMapucheMiSimplificacion extends EditRecord
{
    protected static string $resource = AfipMapucheMiSimplificacionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
