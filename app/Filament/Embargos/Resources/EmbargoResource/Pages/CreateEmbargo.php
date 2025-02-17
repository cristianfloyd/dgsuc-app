<?php

namespace App\Filament\Embargos\Resources\EmbargoResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Embargos\Resources\EmbargoResource;

class CreateEmbargo extends CreateRecord
{
    protected static string $resource = EmbargoResource::class;
}
