<?php

namespace App\Http\Controllers;

use App\Services\Mapuche\DosubaReportService;
use Illuminate\Http\JsonResponse;

class DosubaReportController extends Controller
{
    public function __construct(
        private DosubaReportService $reportService,
    ) {
    }

    public function generate(string $year, string $month): JsonResponse
    {
        $result = $this->reportService->getDosubaReport($year, $month);
        return response()->json($result);
    }
}
