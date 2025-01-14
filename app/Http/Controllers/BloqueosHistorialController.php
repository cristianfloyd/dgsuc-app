<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\View\View;
use App\Models\BloqueosHistorial;
use App\Services\BloqueosHistorialService;

class BloqueosHistorialController extends Controller
{
    public function __construct(
        private readonly BloqueosHistorialService $historialService
    ) {}

    public function index(): View
    {
        $periodos = BloqueosHistorial::select('periodo_importacion')
            ->distinct()
            ->orderByDesc('periodo_importacion')
            ->paginate(12);

        return view('bloqueos.historial.index', compact('periodos'));
    }

    public function show(string $periodo): View
    {
        $estadisticas = $this->historialService->obtenerEstadisticasPeriodo($periodo);

        $registros = BloqueosHistorial::where('periodo_importacion', $periodo)
            ->with(['usuario', 'bloqueo'])
            ->latest()
            ->paginate(25);

        return view('bloqueos.historial.show', compact('registros', 'estadisticas', 'periodo'));
    }
}
