<?php

namespace App\Filament\Embargos\Resources\Embargos\Pages;

use App\Filament\Embargos\Resources\Embargos\EmbargoResource;
use Filament\Resources\Pages\CreateRecord;

class CreateEmbargo extends CreateRecord
{
    protected static string $resource = EmbargoResource::class;
}
