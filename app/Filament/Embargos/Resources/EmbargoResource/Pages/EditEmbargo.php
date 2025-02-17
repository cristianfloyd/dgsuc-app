<?php

namespace App\Filament\Embargos\Resources\EmbargoResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Embargos\Resources\EmbargoResource;

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
