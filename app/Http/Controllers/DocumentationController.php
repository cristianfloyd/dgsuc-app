<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Documentation;

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
}
