<?php

namespace App\Filament\Reportes\Resources\ImportDataResource\Pages;

use Filament\Actions;
use App\Imports\DataImport;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Reportes\Resources\ImportDataResource;

class CreateImportData extends CreateRecord
{
    use WithFileUploads;
    protected static string $resource = ImportDataResource::class;

    public $excel_file = null;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return $data;
    }

    protected function beforeCreate(): void
    {
        // Validación adicional si es necesaria
    }

    protected function afterCreate(): void
    {
        //
    }
}
