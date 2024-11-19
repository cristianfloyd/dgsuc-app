<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use App\Services\Mapuche\DosubaReportService;

class DosubaReportController extends Controller
{
    public function __construct(
        private DosubaReportService $reportService
    ) {}

    public function generate(string $year, string $month): JsonResponse
    {
        $result = $this->reportService->getDosubaReport($year, $month);
        return response()->json($result);
    }
}
