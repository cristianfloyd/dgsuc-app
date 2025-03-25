<?php

namespace App\Filament\Embargos\Resources\EmbargoReportResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Embargos\Resources\EmbargoReportResource;

class EditEmbargo extends EditRecord
{
    protected static string $resource = EmbargoReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
