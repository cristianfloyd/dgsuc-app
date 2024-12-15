<?php

namespace App\Filament\Liquidaciones\Resources\CategoriasBasicosResource\Pages;

use App\Filament\Liquidaciones\Resources\CategoriasBasicosResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCategoriasBasicos extends EditRecord
{
    protected static string $resource = CategoriasBasicosResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
