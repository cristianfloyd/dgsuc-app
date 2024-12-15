<?php

namespace App\Filament\Liquidaciones\Resources\DH21hResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Liquidaciones\Resources\DH21hResource;

class ListDH21hs extends ListRecords
{
    protected static string $resource = DH21hResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //
        ];
    }
}
