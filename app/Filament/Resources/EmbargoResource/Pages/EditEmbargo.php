<?php

namespace App\Filament\Resources\EmbargoResource\Pages;

use App\Filament\Resources\EmbargoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

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
