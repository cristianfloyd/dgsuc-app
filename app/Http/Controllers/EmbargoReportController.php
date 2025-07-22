<?php

namespace App\Http\Controllers;

use App\Services\Reportes\EmbargoReportService;
use Illuminate\Http\Request;

class EmbargoReportController extends Controller
{
    public function generate(Request $request)
    {
        $report = app(EmbargoReportService::class)->generateReport($request->nro_liqui);

        return response()->json($report);
    }
}
