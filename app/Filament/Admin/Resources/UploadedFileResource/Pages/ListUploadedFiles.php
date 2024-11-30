<?php

namespace App\Filament\Admin\Resources\UploadedFileResource\Pages;

use App\Filament\Admin\Resources\UploadedFileResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListUploadedFiles extends ListRecords
{
    protected static string $resource = UploadedFileResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
