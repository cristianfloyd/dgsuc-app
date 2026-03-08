<?php

namespace App\Filament\Reportes\Resources\RepFallecidos\Pages;

use Filament\Actions\DeleteAction;
use App\Filament\Reportes\Resources\RepFallecidos\RepFallecidoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRepFallecido extends EditRecord
{
    protected static string $resource = RepFallecidoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
