<?php

namespace App\Filament\Liquidaciones\Resources\Dh61Resource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Liquidaciones\Resources\Dh61Resource;

class EditDh61 extends EditRecord
{
    protected static string $resource = Dh61Resource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
