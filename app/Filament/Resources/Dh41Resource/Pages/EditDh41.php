<?php

namespace App\Filament\Resources\Dh41Resource\Pages;

use App\Filament\Resources\Dh41Resource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDh41 extends EditRecord
{
    protected static string $resource = Dh41Resource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
