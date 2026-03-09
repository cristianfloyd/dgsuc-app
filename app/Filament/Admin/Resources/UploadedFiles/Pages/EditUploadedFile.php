<?php

namespace App\Filament\Admin\Resources\UploadedFiles\Pages;

use App\Filament\Admin\Resources\UploadedFiles\UploadedFiles\UploadedFileResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditUploadedFile extends EditRecord
{
    protected static string $resource = UploadedFileResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
