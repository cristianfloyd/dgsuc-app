<?php

namespace App\Filament\Admin\Resources\UploadedFiles\Pages;

use App\Filament\Admin\Resources\UploadedFiles\UploadedFiles\UploadedFileResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListUploadedFiles extends ListRecords
{
    protected static string $resource = UploadedFileResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
