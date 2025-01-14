<?php

namespace App\Filament\Reportes\Resources\BloqueosResource\Pages;

use Filament\Actions;
use Livewire\WithFileUploads;
use App\Imports\BloqueosImport;
use Maatwebsite\Excel\Facades\Excel;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Reportes\Resources\BloqueosResource;

class CreateImportData extends CreateRecord
{
    use WithFileUploads;
    protected static string $resource = BloqueosResource::class;

    public $excel_file = null;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return $data;
    }
}
