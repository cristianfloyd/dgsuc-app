<?php

namespace App\Filament\Reportes\Resources\RepGerencialFinalResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Reportes\Resources\RepGerencialFinalResource;

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
