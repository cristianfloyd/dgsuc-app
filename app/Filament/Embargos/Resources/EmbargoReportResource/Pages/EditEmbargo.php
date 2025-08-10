<?php

namespace App\Filament\Embargos\Resources\EmbargoReportResource\Pages;

use App\Filament\Embargos\Resources\EmbargoReportResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

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
