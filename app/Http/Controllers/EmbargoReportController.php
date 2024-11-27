<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\Reportes\EmbargoReportService;

class EmbargoReportController extends Controller
{
    public function generate(Request $request)
    {
        $report = app(EmbargoReportService::class)->generateReport($request->nro_liqui);

        return response()->json($report);
    }
}
