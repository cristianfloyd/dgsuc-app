<?php

namespace App\Filament\Liquidaciones\Resources\Dh61s\Pages;

use App\Filament\Liquidaciones\Resources\Dh61s\Dh61s\Dh61Resource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditDh61 extends EditRecord
{
    protected static string $resource = Dh61Resource::class;

    #[\Override]
    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
