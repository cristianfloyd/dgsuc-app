<?php

namespace App\Filament\Liquidaciones\Resources\LiquidacionControlResource\Pages;

use App\Filament\Liquidaciones\Resources\LiquidacionControlResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLiquidacionControl extends EditRecord
{
    protected static string $resource = LiquidacionControlResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
