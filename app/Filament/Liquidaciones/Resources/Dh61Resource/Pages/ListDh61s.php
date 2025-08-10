<?php

namespace App\Filament\Liquidaciones\Resources\Dh61Resource\Pages;

use App\Filament\Liquidaciones\Resources\Dh61Resource;
use Filament\Resources\Pages\ListRecords;

class ListDh61s extends ListRecords
{
    protected static string $resource = Dh61Resource::class;

    protected function getHeaderActions(): array
    {
        return [

        ];
    }
}
