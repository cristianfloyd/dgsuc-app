<?php

namespace App\Filament\Resources\AfipMapucheArtResource\Pages;

use App\Filament\Resources\AfipMapucheArtResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAfipMapucheArt extends EditRecord
{
    protected static string $resource = AfipMapucheArtResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
