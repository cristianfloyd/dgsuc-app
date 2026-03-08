<?php

namespace App\Filament\Liquidaciones\Resources\CategoriasBasicos\Pages;

use Filament\Actions\DeleteAction;
use App\Filament\Liquidaciones\Resources\CategoriasBasicos\CategoriasBasicosResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCategoriasBasicos extends EditRecord
{
    protected static string $resource = CategoriasBasicosResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
