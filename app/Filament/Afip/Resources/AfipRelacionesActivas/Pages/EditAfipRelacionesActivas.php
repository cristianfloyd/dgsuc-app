<?php

namespace App\Filament\Afip\Resources\AfipRelacionesActivas\Pages;

use App\Filament\Afip\Resources\AfipRelacionesActivas\AfipRelacionesActivas\AfipRelacionesActivasResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditAfipRelacionesActivas extends EditRecord
{
    protected static string $resource = AfipRelacionesActivasResource::class;

    #[\Override]
    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
