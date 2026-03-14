<?php

namespace App\Filament\Reportes\Resources\RepFallecidos\Pages;

use App\Filament\Reportes\Resources\RepFallecidos\RepFallecidos\RepFallecidoResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditRepFallecido extends EditRecord
{
    protected static string $resource = RepFallecidoResource::class;

    #[\Override]
    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
