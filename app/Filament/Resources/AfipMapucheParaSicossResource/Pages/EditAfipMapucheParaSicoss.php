<?php

namespace App\Filament\Resources\AfipMapucheParaSicossResource\Pages;

use App\Filament\Resources\AfipMapucheParaSicossResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAfipMapucheParaSicoss extends EditRecord
{
    protected static string $resource = AfipMapucheParaSicossResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
