<?php

namespace App\Filament\Reportes\Resources\RepGerencialFinalResource\Pages;

use App\Filament\Reportes\Resources\RepGerencialFinalResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRepGerencialFinal extends EditRecord
{
    protected static string $resource = RepGerencialFinalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
