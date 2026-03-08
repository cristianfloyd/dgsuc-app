<?php

namespace App\Filament\Embargos\Resources\EmbargoReports\Pages;

use App\Filament\Embargos\Resources\EmbargoReports\EmbargoReports\EmbargoReportResource;
use Filament\Resources\Pages\CreateRecord;

class CreateEmbargo extends CreateRecord
{
    protected static string $resource = EmbargoReportResource::class;
}
