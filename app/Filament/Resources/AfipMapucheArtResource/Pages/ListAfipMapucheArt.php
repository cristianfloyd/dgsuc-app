<?php

namespace App\Filament\Resources\AfipMapucheArtResource\Pages;

use App\Filament\Resources\AfipMapucheArtResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAfipMapucheArt extends ListRecords
{
    protected static string $resource = AfipMapucheArtResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
