<?php

namespace App\Filament\Reportes\Resources\RepEmbarazadaResource\Pages;

use App\Filament\Reportes\Resources\RepEmbarazadaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRepEmbarazada extends EditRecord
{
    protected static string $resource = RepEmbarazadaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
