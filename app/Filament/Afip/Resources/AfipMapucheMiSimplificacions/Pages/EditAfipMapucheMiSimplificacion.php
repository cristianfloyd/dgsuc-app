<?php

namespace App\Filament\Afip\Resources\AfipMapucheMiSimplificacions\Pages;

use Filament\Actions\DeleteAction;
use App\Filament\Afip\Resources\AfipMapucheMiSimplificacions\AfipMapucheMiSimplificacions\AfipMapucheMiSimplificacionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAfipMapucheMiSimplificacion extends EditRecord
{
    protected static string $resource = AfipMapucheMiSimplificacionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
