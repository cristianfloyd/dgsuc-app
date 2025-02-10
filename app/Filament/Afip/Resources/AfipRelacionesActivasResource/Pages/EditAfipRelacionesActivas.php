<?php

namespace App\Filament\Afip\Resources\AfipRelacionesActivasResource\Pages;

use App\Filament\Afip\Resources\AfipRelacionesActivasResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAfipRelacionesActivas extends EditRecord
{
    protected static string $resource = AfipRelacionesActivasResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
