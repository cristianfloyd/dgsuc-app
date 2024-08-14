<?php

namespace App\Filament\Resources\Dh12Resource\Pages;

use App\Filament\Resources\Dh12Resource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDh12 extends EditRecord
{
    protected static string $resource = Dh12Resource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
