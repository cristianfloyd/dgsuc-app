<?php

namespace App\Http\Controllers;

use App\DTOs\RetUdaDTO;
use Illuminate\Http\Request;
use App\Services\RetUdaService;
use Illuminate\Http\JsonResponse;

class RetUdaController extends Controller
{
    protected $service;

    /**
     * Constructor del controlador RetUda.
     *
     * @param RetUdaService $service
     */
    public function __construct(RetUdaService $service)
    {
        $this->service = $service;
    }

    /**
     * Muestra una lista de todos los RetUda.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $retUdas = $this->service->getAllRetUdas();
        return response()->json($retUdas);
    }

    /**
     * Muestra un RetUda específico.
     *
     * @param int $nroLegaj
     * @param int $nroCargo
     * @param string $periodo
     * @return JsonResponse
     */
    public function show(int $nroLegaj, int $nroCargo, string $periodo): JsonResponse
    {
        $retUda = $this->service->getRetUda($nroLegaj, $nroCargo, $periodo);
        if (!$retUda) {
            return response()->json(['message' => 'RetUda no encontrado'], 404);
        }
        return response()->json($retUda);
    }

    /**
     * Almacena un nuevo RetUda.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $dto = new RetUdaDTO($request->all());
            $retUda = $this->service->createRetUda($dto);
            return response()->json($retUda, 201);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Error al crear RetUda', 'error' => $e->getMessage()], 400);
        }
    }

    /**
     * Actualiza un RetUda existente.
     *
     * @param Request $request
     * @param int $nroLegaj
     * @param int $nroCargo
     * @param string $periodo
     * @return JsonResponse
     */
    public function update(Request $request, int $nroLegaj, int $nroCargo, string $periodo): JsonResponse
    {
        try {
            $dto = new RetUdaDTO($request->all());
            $updated = $this->service->updateRetUda($nroLegaj, $nroCargo, $periodo, $dto);
            if (!$updated) {
                return response()->json(['message' => 'RetUda no encontrado'], 404);
            }
            return response()->json(['message' => 'RetUda actualizado con éxito']);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Error al actualizar RetUda', 'error' => $e->getMessage()], 400);
        }
    }

    /**
     * Elimina un RetUda específico.
     *
     * @param int $nroLegaj
     * @param int $nroCargo
     * @param string $periodo
     * @return JsonResponse
     */
    public function destroy(int $nroLegaj, int $nroCargo, string $periodo): JsonResponse
    {
        $deleted = $this->service->deleteRetUda($nroLegaj, $nroCargo, $periodo);
        if (!$deleted) {
            return response()->json(['message' => 'RetUda no encontrado'], 404);
        }
        return response()->json(['message' => 'RetUda eliminado con éxito']);
    }
}
