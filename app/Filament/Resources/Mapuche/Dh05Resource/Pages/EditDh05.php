<?php

namespace App\Filament\Resources\Mapuche\Dh05Resource\Pages;

use App\Filament\Resources\Mapuche\Dh05Resource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditDh05 extends EditRecord
{
    protected static string $resource = Dh05Resource::class;

    #[\Override]
    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
