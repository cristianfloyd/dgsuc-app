<?php

namespace App\Filament\Reportes\Resources\RepFallecidoResource\Pages;

use App\Filament\Reportes\Resources\RepFallecidoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRepFallecido extends EditRecord
{
    protected static string $resource = RepFallecidoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
