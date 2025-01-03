<?php

namespace App\Filament\Reportes\Resources\RepGerencialfinalResource\Pages;

use App\Filament\Reportes\Resources\RepGerencialfinalResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRepGerencialfinal extends EditRecord
{
    protected static string $resource = RepGerencialfinalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
