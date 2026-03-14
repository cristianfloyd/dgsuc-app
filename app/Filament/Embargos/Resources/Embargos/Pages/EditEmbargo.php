<?php

namespace App\Filament\Embargos\Resources\Embargos\Pages;

use App\Filament\Embargos\Resources\Embargos\Embargos\EmbargoResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditEmbargo extends EditRecord
{
    protected static string $resource = EmbargoResource::class;

    #[\Override]
    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
