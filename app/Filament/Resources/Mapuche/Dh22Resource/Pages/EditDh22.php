<?php

namespace App\Filament\Resources\Mapuche\Dh22Resource\Pages;

use App\Filament\Resources\Mapuche\Dh22Resource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDh22 extends EditRecord
{
    protected static string $resource = Dh22Resource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
