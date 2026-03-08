<?php

namespace App\Filament\Liquidaciones\Resources\Dh61s\Pages;

use Filament\Actions\DeleteAction;
use App\Filament\Liquidaciones\Resources\Dh61s\Dh61Resource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDh61 extends EditRecord
{
    protected static string $resource = Dh61Resource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
