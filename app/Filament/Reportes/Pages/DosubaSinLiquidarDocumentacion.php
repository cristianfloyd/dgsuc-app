<?php

namespace App\Filament\Reportes\Pages;

use Filament\Pages\Page;
use Illuminate\Support\Str;
use League\CommonMark\GithubFlavoredMarkdownConverter;

class DosubaSinLiquidarDocumentacion extends Page
{
    protected static ?string $navigationLabel = 'Documentación';
    protected static ?string $navigationGroup = 'Dosuba';
    protected static ?int $navigationSort = 100;
    protected static ?string $title = 'Documentación del Reporte Dosuba Sin Liquidar';
    protected static string $view = 'filament.pages.dosuba-sin-liquidar-documentacion';

    public function getMarkdownContent(): string
    {
        $markdownPath = base_path('resources/docs/documentacion-dosuba-sin-liquidar.md');
        $markdown = file_get_contents($markdownPath);

        $converter = new GithubFlavoredMarkdownConverter([
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ]);

        return $converter->convert($markdown);
    }
}
