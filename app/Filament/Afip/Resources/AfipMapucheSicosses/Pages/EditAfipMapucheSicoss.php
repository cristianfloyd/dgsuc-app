<?php

namespace App\Filament\Afip\Resources\AfipMapucheSicosses\Pages;

use Filament\Actions\DeleteAction;
use App\Filament\Afip\Resources\AfipMapucheSicosses\AfipMapucheSicosses\AfipMapucheSicossResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAfipMapucheSicoss extends EditRecord
{
    protected static string $resource = AfipMapucheSicossResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
