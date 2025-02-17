<?php

namespace App\Filament\Admin\Resources\DocumentationResource\Pages;

use App\Filament\Admin\Resources\DocumentationResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;
use Filament\Notifications\Notification;

class ListDocumentation extends ListRecords
{
    protected static string $resource = DocumentationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('sync_docs')
                ->label('Sincronizar Documentación')
                ->icon('heroicon-o-arrow-path')
                ->action(function () {
                    try {
                        DocumentationResource::syncMarkdownFiles();

                        Notification::make()
                            ->title('Documentación sincronizada correctamente')
                            ->success()
                            ->send();
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Error al sincronizar documentación')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                })
                ->requiresConfirmation()
                ->modalHeading('¿Sincronizar documentación?')
                ->modalDescription('Esta acción actualizará la documentación desde los archivos markdown. ¿Desea continuar?')
                ->modalSubmitActionLabel('Sí, sincronizar'),
        ];
    }
}
