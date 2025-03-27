<?php

namespace App\Filament\Liquidaciones\Resources\LiquidacionControlResource\Pages;

use App\Filament\Liquidaciones\Resources\LiquidacionControlResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLiquidacionControls extends ListRecords
{
    protected static string $resource = LiquidacionControlResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
