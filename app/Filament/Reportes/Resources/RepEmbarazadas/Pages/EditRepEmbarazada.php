<?php

namespace App\Filament\Reportes\Resources\RepEmbarazadas\Pages;

use Filament\Actions\DeleteAction;
use App\Filament\Reportes\Resources\RepEmbarazadas\RepEmbarazadaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRepEmbarazada extends EditRecord
{
    protected static string $resource = RepEmbarazadaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
