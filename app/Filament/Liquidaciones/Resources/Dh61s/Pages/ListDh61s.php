<?php

namespace App\Filament\Liquidaciones\Resources\Dh61s\Pages;

use App\Filament\Liquidaciones\Resources\Dh61s\Dh61s\Dh61Resource;
use Filament\Resources\Pages\ListRecords;

class ListDh61s extends ListRecords
{
    protected static string $resource = Dh61Resource::class;

    #[\Override]
    protected function getHeaderActions(): array
    {
        return [

        ];
    }
}
