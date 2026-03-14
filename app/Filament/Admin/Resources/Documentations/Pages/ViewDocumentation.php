<?php

namespace App\Filament\Admin\Resources\Documentations\Pages;

use App\Filament\Admin\Resources\Documentations\Documentations\DocumentationResource;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Facades\File;
use League\CommonMark\CommonMarkConverter;

class ViewDocumentation extends Page
{
    protected static string $resource = DocumentationResource::class;

    protected string $view = 'filament.resources.documentation.view';

    #[\Override]
    public function getViewData(): array
    {
        $converter = new CommonMarkConverter();

        // Lee el archivo markdown
        $markdown = File::get(resource_path('docs/index.md'));

        // Convierte a HTML
        $renderedContent = $converter->convert($markdown);

        return [
            'documentation' => $renderedContent,
        ];
    }
}
