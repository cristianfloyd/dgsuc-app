<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Actions\Action;
use Illuminate\Support\Facades\File;
use Filament\Notifications\Notification;
use League\CommonMark\CommonMarkConverter;

class DocumentationPage extends Page
{

    protected static ?string $navigationIcon = 'heroicon-o-book-open';
    protected static ?string $navigationLabel = 'Documentación';
    protected static ?string $title = 'Documentación del Sistema';
    protected static string $view = 'filament.pages.documentation';

    protected static ?string $navigationGroup = 'Ayuda';
    protected static ?int $navigationSort = 100;

    public static function shouldRegisterNavigation(): bool
    {
        return true;
    }

    public string $activeSection = 'index';
    protected array $documentationData = [];
    protected array $markdownFiles = [
        'index' => 'Resumen General',
        'panel-liquidaciones' => 'Panel de Liquidaciones',
        'panel-embargos' => 'Panel de Embargos',
        'panel-admin' => 'Panel Administrativo',
        'recursos' => 'Recursos del Sistema',
        'controles-sicoss' => 'Controles SICOSS',
        'bloqueos' => 'Sistema de Bloqueos'
    ];

   

    public function mount()
    {
        $this->loadDocumentation();
    }



    protected function loadDocumentation()
    {
        $converter = new CommonMarkConverter([
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
            'heading_permalink' => [
                'html_class' => 'heading-permalink',
                'symbol' => '#',
                'insert' => 'before',
            ],
        ]);

        foreach ($this->markdownFiles as $file => $title) {
            $filePath = base_path("docs/filament/{$file}.md");

            if (File::exists($filePath)) {
                $content = File::get($filePath);
                $this->documentationData[] = [
                    'title' => $title,
                    'section' => $file,
                    'content' => $content,
                    'rendered_content' => (string) $converter->convert($content)
                ];
            }
        }

        if (empty($this->documentationData)) {
            Notification::make()
                ->warning()
                ->title('No se encontró documentación')
                ->body('No se encontraron archivos de documentación en el directorio especificado.')
                ->send();
        }
    }

    public function setActiveSection(string $section): void
    {
        $this->activeSection = $section;
    }

    public function print(): void
    {
        $this->dispatch('print-documentation');
    }

    protected function getViewData(): array
    {
        return [
            'documentation' => $this->documentationData,
            'sections' => $this->getSections(),
        ];
    }

    protected function getSections(): array
    {
        return collect($this->markdownFiles)
            ->map(function ($title, $key) {
                return [
                    'key' => $key,
                    'title' => $title,
                ];
            })
            ->toArray();
    }

    public function getHeadings(): array
    {
        $currentDoc = collect($this->documentationData)
            ->firstWhere('section', $this->activeSection);

        if (!$currentDoc) {
            return [];
        }

        preg_match_all('/#+ (.*)/', $currentDoc['content'], $matches);
        return $matches[1] ?? [];
    }
}
