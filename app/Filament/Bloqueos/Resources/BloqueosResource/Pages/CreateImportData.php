<?php

namespace App\Filament\Bloqueos\Resources\BloqueosResource\Pages;

use App\Filament\Bloqueos\Resources\BloqueosResource;
use Filament\Resources\Pages\CreateRecord;
use Livewire\WithFileUploads;

class CreateImportData extends CreateRecord
{
    use WithFileUploads;

    public $excel_file;

    protected static string $resource = BloqueosResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return $data;
    }
}
