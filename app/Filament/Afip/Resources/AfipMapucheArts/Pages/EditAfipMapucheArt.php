<?php

namespace App\Filament\Afip\Resources\AfipMapucheArts\Pages;

use App\Filament\Afip\Resources\AfipMapucheArts\AfipMapucheArts\AfipMapucheArtResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditAfipMapucheArt extends EditRecord
{
    protected static string $resource = AfipMapucheArtResource::class;

    #[\Override]
    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
