<?php

namespace App\Filament\Resources\Dh03Resource\Pages;

use App\Filament\Resources\Dh03Resource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditDh03 extends EditRecord
{
    protected static string $resource = Dh03Resource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
