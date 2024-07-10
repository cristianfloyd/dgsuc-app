<?php

namespace App\Filament\Resources\AfipMapucheMiSimplificacionResource\Pages;

use App\Filament\Resources\AfipMapucheMiSimplificacionResource;
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
