<?php

namespace App\Filament\Reportes\Resources\DosubaSinLiquidarResource\Pages;

use App\Filament\Reportes\Resources\DosubaSinLiquidarResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDosubaSinLiquidar extends EditRecord
{
    protected static string $resource = DosubaSinLiquidarResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
