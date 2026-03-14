<?php

namespace App\Filament\Afip\Resources\AfipMapucheSicosses\Pages;

use App\Filament\Afip\Resources\AfipMapucheSicosses\AfipMapucheSicosses\AfipMapucheSicossResource;
use App\Filament\Resources\Pages\BaseListRecords;
use App\Repositories\AfipMapucheSicossRepository;
use App\Services\AfipMapucheSicossTableService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListAfipMapucheSicosses extends BaseListRecords
{
    protected static string $resource = AfipMapucheSicossResource::class;

    #[\Override]
    public function mount(): void
    {
        parent::mount();
    }

    #[\Override]
    public function getTabs(): array
    {
        return [
            'todos' => Tab::make('Todos'),
            'diferencia_negativa' => Tab::make('Diferencia Negativa')
                ->icon('heroicon-m-arrow-trending-down')
                ->modifyQueryUsing(fn(Builder $query) => $query->whereRaw('rem_total - rem_impo6 < 0')),
        ];
    }

    protected function getTableServiceClass(): string
    {
        return AfipMapucheSicossTableService::class;
    }

    #[\Override]
    protected function getHeaderActions(): array
    {
        return [

            Action::make('truncate')
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
