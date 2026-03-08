<?php

namespace App\Filament\Admin\Resources\UploadedFiles\Pages;

use App\Filament\Admin\Resources\UploadedFiles\UploadedFileResource;
use Filament\Resources\Pages\CreateRecord;

class CreateUploadedFile extends CreateRecord
{
    protected static string $resource = UploadedFileResource::class;
}
