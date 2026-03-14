<?php

namespace App\Filament\Embargos\Resources\EmbargoReports\Pages;

use App\Filament\Embargos\Resources\EmbargoReports\EmbargoReports\EmbargoReportResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditEmbargo extends EditRecord
{
    protected static string $resource = EmbargoReportResource::class;

    #[\Override]
    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
