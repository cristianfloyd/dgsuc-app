<?php

namespace App\Filament\Admin\Resources\Documentations\Pages;

use App\Filament\Admin\Resources\Documentations\Documentations\DocumentationResource;
use Filament\Resources\Pages\CreateRecord;

class CreateDocumentation extends CreateRecord
{
    protected static string $resource = DocumentationResource::class;
}
