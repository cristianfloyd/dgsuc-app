<?php

namespace App\Filament\Afip\Resources\AfipMapucheSicossCalculos\Pages;

use App\Filament\Afip\Resources\AfipMapucheSicossCalculos\AfipMapucheSicossCalculos\AfipMapucheSicossCalculoResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Override;

class EditAfipMapucheSicossCalculo extends EditRecord
{
    protected static string $resource = AfipMapucheSicossCalculoResource::class;

    #[Override]
    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
