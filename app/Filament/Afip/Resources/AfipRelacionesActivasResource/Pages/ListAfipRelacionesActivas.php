<?php

namespace App\Filament\Afip\Resources\AfipRelacionesActivasResource\Pages;

use App\Filament\Afip\Resources\AfipRelacionesActivasResource;
use App\Filament\Afip\Resources\AfipRelacionesActivasResource\Actions\ImportAction;
use App\Filament\Afip\Resources\AfipRelacionesActivasResource\Actions\TruncateAction;
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
