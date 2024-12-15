<?php

namespace App\Filament\Liquidaciones\Resources\Dh21Resource\Pages;

use App\Filament\Liquidaciones\Resources\Dh21Resource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDh21 extends EditRecord
{
    protected static string $resource = Dh21Resource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
