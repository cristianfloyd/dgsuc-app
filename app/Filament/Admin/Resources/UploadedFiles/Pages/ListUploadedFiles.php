<?php

namespace App\Filament\Admin\Resources\UploadedFiles\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Admin\Resources\UploadedFiles\UploadedFileResource;
use Filament\Actions;
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
