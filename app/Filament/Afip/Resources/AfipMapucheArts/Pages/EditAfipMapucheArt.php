<?php

namespace App\Filament\Afip\Resources\AfipMapucheArts\Pages;

use Filament\Actions\DeleteAction;
use App\Filament\Afip\Resources\AfipMapucheArts\AfipMapucheArts\AfipMapucheArtResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAfipMapucheArt extends EditRecord
{
    protected static string $resource = AfipMapucheArtResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
