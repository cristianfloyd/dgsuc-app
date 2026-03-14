<?php

namespace App\Filament\Resources\Dh41Resource\Pages;

use App\Filament\Resources\Dh41Resource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDh41s extends ListRecords
{
    protected static string $resource = Dh41Resource::class;

    #[\Override]
    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
