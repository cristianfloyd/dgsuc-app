<?php

namespace App\Filament\Afip\Resources\AfipRelacionesActivas\Pages;

use App\Filament\Afip\Resources\AfipRelacionesActivas\Actions\ImportAction;
use App\Filament\Afip\Resources\AfipRelacionesActivas\Actions\TruncateAction;
use App\Filament\Afip\Resources\AfipRelacionesActivas\AfipRelacionesActivas\AfipRelacionesActivasResource;
use Filament\Resources\Pages\ListRecords;

class ListAfipRelacionesActivas extends ListRecords
{
    protected static string $resource = AfipRelacionesActivasResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ImportAction::make(),
            TruncateAction::make(),
        ];
    }
}
