<?php

namespace App\Filament\Reportes\Resources\RepGerencialFinalResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Reportes\Resources\RepGerencialfinalResource;

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
