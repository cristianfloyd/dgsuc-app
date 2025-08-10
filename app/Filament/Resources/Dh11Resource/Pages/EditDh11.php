<?php

namespace App\Filament\Resources\Dh11Resource\Pages;

use App\Filament\Resources\Dh11Resource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDh11 extends EditRecord
{
    protected static string $resource = Dh11Resource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->previousUrl ?? $this->getResource()::getUrl('index');
    }
}
