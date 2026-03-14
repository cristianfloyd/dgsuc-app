<?php

namespace App\Filament\Afip\Resources\AfipMapucheMiSimplificacions\Pages;

use App\Filament\Afip\Resources\AfipMapucheMiSimplificacions\AfipMapucheMiSimplificacions\AfipMapucheMiSimplificacionResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditAfipMapucheMiSimplificacion extends EditRecord
{
    protected static string $resource = AfipMapucheMiSimplificacionResource::class;

    #[\Override]
    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
