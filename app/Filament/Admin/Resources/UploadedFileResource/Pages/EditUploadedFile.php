<?php

namespace App\Filament\Admin\Resources\UploadedFileResource\Pages;

use App\Filament\Admin\Resources\UploadedFileResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditUploadedFile extends EditRecord
{
    protected static string $resource = UploadedFileResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
