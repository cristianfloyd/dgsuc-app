<?php

namespace App\Filament\Afip\Actions;

use App\Filament\Afip\Handlers\SicossControlActionHandler;
use Filament\Actions\Action;

class EjecutarControlContribucionesAction extends Action
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Ejecutar Control de Contribuciones')
            ->icon('heroicon-o-banknotes')
            ->color('primary')
            ->requiresConfirmation()
            ->modalHeading('¿Ejecutar control de contribuciones?')
            ->modalDescription('Esta acción ejecutará el control específico de contribuciones para el período fiscal actual.')
            ->modalSubmitActionLabel('Sí, ejecutar')
            ->modalCancelActionLabel('Cancelar')
            ->action(function (): void {
                app(SicossControlActionHandler::class)->ejecutarControl(
                    'contribuciones',
                    $this->getLivewire(),
                );
            });
    }

    public static function getDefaultName(): ?string
    {
        return 'ejecutar_control_contribuciones';
    }

    public function withPeriodBadge(): static
    {
        return $this->badge(function () {
            $livewire = $this->getLivewire();
            return \sprintf('%d-%02d', $livewire->year, $livewire->month);
        });
    }
}
