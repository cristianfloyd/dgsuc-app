<?php

namespace App\Filament\Afip\Resources\AfipMapucheMiSimplificacionResource\Pages;

use App\Filament\Afip\Resources\AfipMapucheMiSimplificacionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAfipMapucheMiSimplificacions extends ListRecords
{
    protected static string $resource = AfipMapucheMiSimplificacionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
