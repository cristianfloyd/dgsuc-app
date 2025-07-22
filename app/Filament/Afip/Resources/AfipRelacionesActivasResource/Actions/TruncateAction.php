<?php

namespace App\Filament\Afip\Resources\AfipRelacionesActivasResource\Actions;

use App\Models\AfipRelacionesActivas;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

class TruncateAction extends Action
{
    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label('Truncar Tabla')
            ->icon('heroicon-o-trash')
            ->color('danger')
            ->requiresConfirmation()
            ->modalHeading('Truncar Tabla')
            ->modalDescription('¿Está seguro que desea eliminar todos los registros? Esta acción no se puede deshacer.')
            ->modalSubmitActionLabel('Sí, eliminar todo')
            ->modalCancelActionLabel('No, cancelar')
            ->action(function (): void {
                AfipRelacionesActivas::query()->truncate();

                Notification::make()
                    ->title('Tabla truncada correctamente')
                    ->success()
                    ->send();
            });
    }

    public static function getDefaultName(): ?string
    {
        return 'truncate';
    }
}
