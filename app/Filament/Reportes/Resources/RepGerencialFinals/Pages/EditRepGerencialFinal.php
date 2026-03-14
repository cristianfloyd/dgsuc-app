<?php

namespace App\Filament\Reportes\Resources\RepGerencialFinals\Pages;

use App\Filament\Reportes\Resources\RepGerencialFinals\RepGerencialFinals\RepGerencialFinalResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditRepGerencialFinal extends EditRecord
{
    protected static string $resource = RepGerencialFinalResource::class;

    #[\Override]
    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
