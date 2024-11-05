<?php

namespace App\Filament\Resources\Dh61Resource\Pages;

use App\Filament\Resources\Dh61Resource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDh61s extends ListRecords
{
    protected static string $resource = Dh61Resource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
