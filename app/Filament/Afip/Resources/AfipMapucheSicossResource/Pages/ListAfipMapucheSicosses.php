<?php

namespace App\Filament\Afip\Resources\AfipMapucheSicossResource\Pages;

use Filament\Actions;
use Illuminate\Support\Facades\Log;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use App\Services\AfipMapucheSicossTableService;
use App\Traits\FilamentTableInitializationTrait;
use App\Filament\Resources\Pages\BaseListRecords;
use App\Contracts\TableService\TableServiceInterface;
use App\Services\TableManager\TableInitializationManager;
use App\Filament\Afip\Resources\AfipMapucheSicossResource;

class ListAfipMapucheSicosses extends BaseListRecords
{


    protected static string $resource = AfipMapucheSicossResource::class;


    public function mount(): void
    {
        Log::info("ListAfipMapucheSicosses::mount");
        parent::mount();
    }


    protected function getTableServiceClass(): string
    {
        return AfipMapucheSicossTableService::class;
    }
    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('exportarSicoss')
                ->label('Exportar SICOSS')
                ->icon('heroicon-o-document-arrow-down')
                ->action(function () {
                    $path = AfipMapucheSicossResource::generarArchivoSicoss();
                    return response()->download($path)->deleteFileAfterSend();
                })
                ->color('success'),
            Actions\Action::make('importar')
                            ->label('Importar')
                            ->url(fn (): string => static::$resource::getUrl('import')),
        ];
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
}
