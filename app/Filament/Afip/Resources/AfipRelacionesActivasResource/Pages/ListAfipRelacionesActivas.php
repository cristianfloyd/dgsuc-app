<?php

namespace App\Filament\Afip\Resources\AfipRelacionesActivasResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Afip\Resources\AfipRelacionesActivasResource;
use App\Filament\Afip\Resources\AfipRelacionesActivasResource\Actions\ImportAction;
use App\Filament\Afip\Resources\AfipRelacionesActivasResource\Actions\TruncateAction;

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
