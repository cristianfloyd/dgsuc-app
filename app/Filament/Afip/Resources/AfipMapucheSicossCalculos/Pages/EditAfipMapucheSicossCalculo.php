<?php

namespace App\Filament\Afip\Resources\AfipMapucheSicossCalculos\Pages;

use Filament\Actions\DeleteAction;
use App\Filament\Afip\Resources\AfipMapucheSicossCalculos\AfipMapucheSicossCalculoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAfipMapucheSicossCalculo extends EditRecord
{
    protected static string $resource = AfipMapucheSicossCalculoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
