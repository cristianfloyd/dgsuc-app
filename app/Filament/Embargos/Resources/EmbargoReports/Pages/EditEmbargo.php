<?php

namespace App\Filament\Embargos\Resources\EmbargoReports\Pages;

use Filament\Actions\DeleteAction;
use App\Filament\Embargos\Resources\EmbargoReports\EmbargoReportResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEmbargo extends EditRecord
{
    protected static string $resource = EmbargoReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
