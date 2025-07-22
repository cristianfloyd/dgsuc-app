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

    public function notifyImportResults(int $processedCount, int $duplicatesCount): void
    {
        $message = $this->buildImportResultMessage($processedCount, $duplicatesCount);

        Notification::make()
            ->title('Importación Finalizada')
            ->body($message)
            ->success()
            ->send();
    }

    /**
     * Construye el mensaje de resultados de la importación.
     *
     * @param int $processedCount Número total de registros procesados
     * @param int $duplicatesCount Número de registros duplicados encontrados
     *
     * @return string Mensaje formateado con los resultados de la importación
     */
    private function buildImportResultMessage(int $processedCount, int $duplicatesCount): string
    {
        $message = "Importación completada.\n";
        $message .= "Registros procesados: {$processedCount}\n";

        if ($duplicatesCount > 0) {
            $message .= "Se encontraron {$duplicatesCount} registros duplicados para revisión.";
        }

        return $message;
    }
}
