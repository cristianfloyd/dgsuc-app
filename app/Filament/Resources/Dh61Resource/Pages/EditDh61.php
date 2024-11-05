<?php

namespace App\Filament\Resources\Dh61Resource\Pages;

use App\Filament\Resources\Dh61Resource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

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
