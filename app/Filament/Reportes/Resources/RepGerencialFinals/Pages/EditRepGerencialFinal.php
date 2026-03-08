<?php

namespace App\Filament\Reportes\Resources\RepGerencialFinals\Pages;

use Filament\Actions\DeleteAction;
use App\Filament\Reportes\Resources\RepGerencialFinals\RepGerencialFinals\RepGerencialFinalResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRepGerencialFinal extends EditRecord
{
    protected static string $resource = RepGerencialFinalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
