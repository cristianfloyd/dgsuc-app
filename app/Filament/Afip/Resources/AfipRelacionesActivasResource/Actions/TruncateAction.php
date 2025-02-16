<?php

namespace App\Filament\Afip\Resources\AfipRelacionesActivasResource\Actions;

use Filament\Actions\Action;
use Illuminate\Support\Facades\DB;
use App\Models\AfipRelacionesActivas;
use Filament\Notifications\Notification;

class TruncateAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'truncate';
    }

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
}
