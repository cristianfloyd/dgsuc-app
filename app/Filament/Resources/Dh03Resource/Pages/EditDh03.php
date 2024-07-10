<?php

namespace App\Filament\Resources\Dh03Resource\Pages;

use App\Filament\Resources\Dh03Resource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDh03 extends EditRecord
{
    protected static string $resource = Dh03Resource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
