<?php

namespace App\Filament\Reportes\Resources\RepEmbarazadas\Pages;

use App\Filament\Reportes\Resources\RepEmbarazadas\RepEmbarazadas\RepEmbarazadaResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditRepEmbarazada extends EditRecord
{
    protected static string $resource = RepEmbarazadaResource::class;

    #[\Override]
    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
