<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Documentation;
use League\CommonMark\CommonMarkConverter;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\HeadingPermalink\HeadingPermalinkExtension;
use League\CommonMark\Extension\TableOfContents\TableOfContentsExtension;

class DocumentationController extends Controller
{
    public function download()
    {
        $documentation = Documentation::where('is_published', true)
            ->orderBy('section')
            ->orderBy('order')
            ->get();

        $pdf = PDF::loadView('pdf.documentation', [
            'documentation' => $documentation
        ]);

        return $pdf->download('documentacion.pdf');
    }

    public function show($slug)
    {
        $documentation = Documentation::where('slug', $slug)
            ->where('is_published', true)
            ->firstOrFail();

        // Configurar el ambiente de CommonMark con las extensiones necesarias
        $environment = new Environment([
            'heading_permalink' => [
                'html_class' => 'heading-permalink',
                'symbol' => '#',
                'insert' => 'before',
            ],
            'table_of_contents' => [
                'position' => 'placeholder',
                'style' => 'bullet',
                'min_heading_level' => 2,
                'max_heading_level' => 4,
                'normalize' => 'relative',
            ],
        ]);

        $environment->addExtension(new CommonMarkCoreExtension());
        $environment->addExtension(new HeadingPermalinkExtension());
        $environment->addExtension(new TableOfContentsExtension());

        $converter = new CommonMarkConverter([], $environment);
        $htmlContent = $converter->convert($documentation->content);

        // Extraer los encabezados para el TOC lateral
        preg_match_all('/<h([2-4]).*?id="(.*?)".*?>(.*?)<\/h[2-4]>/i', $htmlContent, $matches, PREG_SET_ORDER);

        $tableOfContents = collect($matches)->map(function ($match) {
            return [
                'level' => (int) $match[1],
                'id' => $match[2],
                'title' => strip_tags($match[3]),
            ];
        });

        // Obtener documentos anterior y siguiente
        $previousDoc = Documentation::where('is_published', true)
            ->where('section', $documentation->section)
            ->where('order', '<', $documentation->order)
            ->orderBy('order', 'desc')
            ->first();

        $nextDoc = Documentation::where('is_published', true)
            ->where('section', $documentation->section)
            ->where('order', '>', $documentation->order)
            ->orderBy('order', 'asc')
            ->first();

        return view('documentation.show', [
            'documentation' => $documentation,
            'content' => $htmlContent,
            'tableOfContents' => $tableOfContents,
            'previousDoc' => $previousDoc,
            'nextDoc' => $nextDoc,
        ]);
    }

    public function index()
    {
        $docs = Documentation::where('is_published', true)
            ->orderBy('section')
            ->orderBy('order')
            ->get();

        return view('documentation.index', [
            'docs' => $docs,
        ]);
    }
}
