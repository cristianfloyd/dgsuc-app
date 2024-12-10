<?php

namespace App\Filament\Reportes\Resources\EmbargoResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Reportes\Resources\EmbargoResource;

class EditEmbargo extends EditRecord
{
    protected static string $resource = EmbargoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
