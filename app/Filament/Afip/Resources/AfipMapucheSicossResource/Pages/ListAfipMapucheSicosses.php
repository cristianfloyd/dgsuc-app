<?php

namespace App\Filament\Afip\Resources\AfipMapucheSicossResource\Pages;

use Filament\Actions;
use Illuminate\Support\Facades\Log;
use Filament\Resources\Pages\ListRecords;
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
}
