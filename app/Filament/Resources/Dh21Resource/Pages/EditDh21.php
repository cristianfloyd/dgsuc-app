<?php

namespace App\Filament\Resources\Dh21Resource\Pages;

use App\Filament\Resources\Dh21Resource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditDh21 extends EditRecord
{
    protected static string $resource = Dh21Resource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
