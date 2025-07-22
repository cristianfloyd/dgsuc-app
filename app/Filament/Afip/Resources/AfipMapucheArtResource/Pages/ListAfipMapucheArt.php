<?php

namespace App\Filament\Afip\Resources\AfipMapucheArtResource\Pages;

use App\Exports\AfipMapucheArtExport;
use App\Filament\Actions\PoblarAfipArtAction;
use App\Filament\Afip\Resources\AfipMapucheArtResource;
use App\Models\AfipMapucheArt;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\ActionSize;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Facades\Excel;

class ListAfipMapucheArt extends ListRecords
{
    protected static string $resource = AfipMapucheArtResource::class;

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('Todos')
                ->icon('heroicon-o-users'),
            'sin_legajo' => Tab::make('Sin Legajo')
                ->icon('heroicon-o-exclamation-circle')
                ->badge(AfipMapucheArt::whereNull('nro_legaj')->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->whereNull('nro_legaj')),
            'con_legajo' => Tab::make('Con Legajo')
                ->icon('heroicon-o-check-circle')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereNotNull('nro_legaj')),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('export')
                ->label('Exportar a Excel')
                ->icon('heroicon-o-document-arrow-down')
                ->action(function ($livewire) {
                    return Excel::download(
                        new AfipMapucheArtExport(
                            session('periodo_fiscal', date('Ym')),
                            $livewire->getFilteredTableQuery(),
                        ),
                        'reporte-afip-art-' . session('periodo_fiscal', date('Ym')) . '.xlsx',
                    );
                }),
            PoblarAfipArtAction::make()
                ->label('Poblar ART')
                ->modalHeading('Confirmación de Poblado ART')
                ->modalDescription('
                    **¡Importante!** Antes de continuar, asegúrese de que:

                    - El recurso "Controles SICOSS" contenga datos para el período fiscal seleccionado.
                    - El período fiscal ingresado coincida con los datos que desea procesar.
                    - Este proceso puede tomar varios minutos dependiendo de la cantidad de registros.
                ')
                ->modalSubmitActionLabel('Sí, poblar datos')
                ->modalIcon('heroicon-o-arrow-up-tray')
                ->color('success')
                ->size(ActionSize::Large)
                ->icon('heroicon-o-arrow-up-tray')
                ->slideOver(),

            Action::make('vaciar_tabla')
                ->label('Vaciar Tabla')
                ->modalHeading('Confirmación para Vaciar Tabla')
                ->modalDescription('
                    **¡Advertencia!** Esta acción:

                    - Eliminará **TODOS** los registros de la tabla ART.
                    - Es irreversible.
                    - Se recomienda hacer una copia de seguridad antes de proceder.
                ')
                ->modalSubmitActionLabel('Sí, vaciar tabla')
                ->modalIcon('heroicon-o-trash')
                ->color('danger')
                ->size(ActionSize::Large)
                ->icon('heroicon-o-trash')
                ->requiresConfirmation()
                ->action(function (): void {
                    try {
                        AfipMapucheArt::truncate();
                        Notification::make()
                            ->title('Tabla vaciada exitosamente')
                            ->success()
                            ->send();
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Error al vaciar la tabla')
                            ->body('No se pudo completar la operación')
                            ->danger()
                            ->send();
                    }
                }),
        ];
    }
}
