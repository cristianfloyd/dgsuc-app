<?php

namespace App\Filament\Admin\Resources\UploadedFileResource\Pages;

use App\Filament\Admin\Resources\UploadedFileResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateUploadedFile extends CreateRecord
{
    protected static string $resource = UploadedFileResource::class;
}
