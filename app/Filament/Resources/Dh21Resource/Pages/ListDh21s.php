<?php

namespace App\Filament\Resources\Dh21Resource\Pages;

use App\Filament\Resources\Dh21Resource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDh21s extends ListRecords
{
    protected static string $resource = Dh21Resource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
