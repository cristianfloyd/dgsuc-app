<?php

namespace App\Filament\Resources\Mapuche\Dh21hResource\Pages;

use App\Filament\Resources\Mapuche\Dh21hResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDh21h extends EditRecord
{
    protected static string $resource = Dh21hResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
