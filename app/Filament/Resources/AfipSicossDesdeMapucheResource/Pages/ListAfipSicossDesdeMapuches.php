<?php

namespace App\Filament\Resources\AfipSicossDesdeMapucheResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\AfipSicossDesdeMapucheResource;

class ListAfipSicossDesdeMapuches extends ListRecords
{
    protected static string $resource = AfipSicossDesdeMapucheResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
