<?php

namespace App\Filament\Afip\Resources\AfipMapucheSicossCalculoResource\Pages;

use App\Filament\Afip\Resources\AfipMapucheSicossCalculoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAfipMapucheSicossCalculo extends EditRecord
{
    protected static string $resource = AfipMapucheSicossCalculoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
