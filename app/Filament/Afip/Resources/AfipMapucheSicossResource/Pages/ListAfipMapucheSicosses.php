<?php

namespace App\Filament\Afip\Resources\AfipMapucheSicossResource\Pages;

use App\Filament\Afip\Resources\AfipMapucheSicossResource;
use App\Filament\Resources\Pages\BaseListRecords;
use App\Repositories\AfipMapucheSicossRepository;
use App\Services\AfipMapucheSicossTableService;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListAfipMapucheSicosses extends BaseListRecords
{
    protected static string $resource = AfipMapucheSicossResource::class;

    public function mount(): void
    {
        parent::mount();
    }

    public function getTabs(): array
    {
        return [
            'todos' => Tab::make('Todos'),
            'diferencia_negativa' => Tab::make('Diferencia Negativa')
                ->icon('heroicon-m-arrow-trending-down')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereRaw('rem_total - rem_impo6 < 0')),
        ];
    }

    protected function getTableServiceClass(): string
    {
        return AfipMapucheSicossTableService::class;
    }

    protected function getHeaderActions(): array
    {
        return [

            Actions\Action::make('truncate')
                ->label('Borrar Tabla')
                ->action(function (): void {
                    $repository = new AfipMapucheSicossRepository();
                    $repository->truncate();
                    Notification::make()
                        ->title('Datos Eliminados')
                        ->success()
                        ->send();
                })
                ->color('danger')
                ->requiresConfirmation(),
        ];
    }
}
