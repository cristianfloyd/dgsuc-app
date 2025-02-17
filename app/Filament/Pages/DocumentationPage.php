<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\Documentation;
use League\CommonMark\CommonMarkConverter;
use Filament\Support\Colors\Color;

class DocumentationPage extends Page
{
    public $activeSection = 'general';
    protected $listeners = ['darkModeChanged'];

    protected static ?string $navigationIcon = 'heroicon-o-book-open';
    protected static ?string $navigationLabel = 'Documentación';
    protected static ?string $title = 'Documentación del Sistema';

    protected static string $view = 'filament.pages.documentation';

    public $documentation;

    public function mount()
    {
        $this->documentation = Documentation::where('is_published', true)
            ->orderBy('section')
            ->orderBy('order')
            ->get()
            ->map(function ($doc) {
                $doc->rendered_content = $this->renderMarkdown($doc->content);
                return $doc;
            });
    }

    public function setActiveSection($section)
    {
        $this->activeSection = $section;
    }

    public function darkModeChanged()
    {
        // Re-renderizar contenido con estilos dark/light
        $this->documentation = $this->documentation->map(function ($doc) {
            $doc->rendered_content = $this->renderMarkdown($doc->content);
            return $doc;
        });
    }

    protected function renderMarkdown($content)
    {
        $converter = new CommonMarkConverter([
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ]);

        return $converter->convert($content);
    }

    public function print()
    {
        $this->dispatch('print-documentation');
    }

    public function getViewData(): array
    {
        $converter = new CommonMarkConverter();
        $markdown = $this->renderMarkdown($this->documentation->first()->content);

        return [
            'documentation' => $markdown,
            'sections' => $this->getSections(),
        ];
    }

    protected function getSections(): array
    {
        return [
            'general' => 'Documentación General',
            'liquidaciones' => 'Panel de Liquidaciones',
            'embargos' => 'Panel de Embargos',
            'reportes' => 'Panel de Reportes',
            'admin' => 'Panel Administrativo'
        ];
    }
}
