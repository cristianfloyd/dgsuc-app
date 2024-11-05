<?php

namespace App\Filament\Resources\Dh13Resource\Pages;

use Filament\Actions;
use App\Filament\Resources\Dh13Resource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateDh13 extends CreateRecord
{
    protected static string $resource = Dh13Resource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function afterCreate(): void
    {
        Notification::make()
            ->title('Registro creado exitosamente')
            ->success()
            ->send();
    }
}
