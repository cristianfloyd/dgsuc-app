<?php

namespace App\Filament\Resources\Mapuche\Dh05Resource\Pages;

use App\Filament\Resources\Mapuche\Dh05Resource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDh05 extends EditRecord
{
    protected static string $resource = Dh05Resource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
