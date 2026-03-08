<?php

namespace App\Filament\Admin\Resources\UploadedFiles\Pages;

use Filament\Actions\DeleteAction;
use App\Filament\Admin\Resources\UploadedFiles\UploadedFileResource;
use Filament\Actions;
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
