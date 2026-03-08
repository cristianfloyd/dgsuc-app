<?php

namespace App\Filament\Liquidaciones\Resources\LiquidacionControls\Pages;

use Filament\Actions\DeleteAction;
use App\Filament\Liquidaciones\Resources\LiquidacionControls\LiquidacionControlResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLiquidacionControl extends EditRecord
{
    protected static string $resource = LiquidacionControlResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
