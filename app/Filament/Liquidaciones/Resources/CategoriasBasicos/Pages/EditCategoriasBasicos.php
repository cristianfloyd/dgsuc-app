<?php

namespace App\Filament\Liquidaciones\Resources\CategoriasBasicos\Pages;

use App\Filament\Liquidaciones\Resources\CategoriasBasicos\CategoriasBasicos\CategoriasBasicosResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCategoriasBasicos extends EditRecord
{
    protected static string $resource = CategoriasBasicosResource::class;

    #[\Override]
    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
