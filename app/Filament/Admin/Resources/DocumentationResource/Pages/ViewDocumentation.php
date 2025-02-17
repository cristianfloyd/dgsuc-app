<?php

namespace App\Filament\Admin\Resources\DocumentationResource\Pages;

use Filament\Resources\Pages\Page;
use Illuminate\Support\Facades\File;
use League\CommonMark\CommonMarkConverter;
use App\Filament\Admin\Resources\DocumentationResource;

class ViewDocumentation extends Page
{
    protected static string $resource = DocumentationResource::class;
    protected static string $view = 'filament.resources.documentation.view';

    public function getViewData(): array
    {
        $converter = new CommonMarkConverter();

        // Lee el archivo markdown
        $markdown = File::get(resource_path('docs/index.md'));

        // Convierte a HTML
        $html = $converter->convert($markdown);

        return [
            'documentation' => $html,
        ];
    }
}
