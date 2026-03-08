<?php

namespace App\Filament\Embargos\Resources\Embargos\Pages;

use Filament\Actions\DeleteAction;
use App\Filament\Embargos\Resources\Embargos\EmbargoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEmbargo extends EditRecord
{
    protected static string $resource = EmbargoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
