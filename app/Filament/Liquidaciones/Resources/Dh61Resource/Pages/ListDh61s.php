<?php

namespace App\Filament\Liquidaciones\Resources\Dh61Resource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Liquidaciones\Resources\Dh61Resource;

class ListDh61s extends ListRecords
{
    protected static string $resource = Dh61Resource::class;

    protected function getHeaderActions(): array
    {
        return [
            //
        ];
    }
}
