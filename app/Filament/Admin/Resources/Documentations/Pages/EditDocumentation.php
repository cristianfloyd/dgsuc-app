<?php

namespace App\Filament\Admin\Resources\Documentations\Pages;

use App\Filament\Admin\Resources\Documentations\Documentations\DocumentationResource;
use Filament\Resources\Pages\EditRecord;

class EditDocumentation extends EditRecord
{
    protected static string $resource = DocumentationResource::class;
}
