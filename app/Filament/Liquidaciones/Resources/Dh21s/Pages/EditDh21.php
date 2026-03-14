<?php

namespace App\Filament\Liquidaciones\Resources\Dh21s\Pages;

use App\Filament\Liquidaciones\Resources\Dh21s\Dh21s\Dh21Resource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditDh21 extends EditRecord
{
    protected static string $resource = Dh21Resource::class;

    #[\Override]
    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
