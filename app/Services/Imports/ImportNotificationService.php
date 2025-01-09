<?php

namespace App\Services\Imports;

use Filament\Notifications\Notification;

class ImportNotificationService
{
    public function sendSuccessNotification(): void
    {
        Notification::make()
            ->title('Importación exitosa')
            ->success()
            ->send();
    }

    public function sendErrorNotification(string $message): void
    {
        Notification::make()
            ->title('Error en la importación')
            ->body("Error: {$message}")
            ->danger()
            ->send();
    }

    public function sendWarningNotification(string $title, string $message): void
    {
        Notification::make()
            ->title($title)
            ->body($message)
            ->warning()
            ->send();
    }
}
