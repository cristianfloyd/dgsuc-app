<?php

namespace App\Http\Controllers;

use App\Services\Dh92Service;
use Illuminate\Http\Request;
use Exception;

class Dh92Controller extends Controller
{
    /**
     * @var Dh92Service
     */
    protected $service;

    /**
     * Constructor del controlador.
     *
     * @param Dh92Service $service
     */
    public function __construct(Dh92Service $service)
    {
        $this->service = $service;
    }

    /**
     * Almacena un nuevo registro.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'nrolegajo' => 'nullable|integer',
                'codn_banco' => 'nullable|integer',
                'codn_sucur' => 'nullable|integer',
                'tipo_cuent' => 'nullable|string|size:2',
                'nro_cuent' => 'nullable|numeric',
                'codn_verif' => 'nullable|integer',
                'nrovalorpago' => 'nullable|integer',
                'cbu' => 'nullable|string|max:25',
            ]);

            $result = $this->service->createWithTransaction($data);
            return response()->json(['message' => 'Registro creado con éxito', 'data' => $result], 201);
        } catch (Exception $e) {
            return response()->json(['message' => 'Error al crear el registro', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Actualiza un registro existente.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        try {
            $data = $request->validate([
                'nrolegajo' => 'nullable|integer',
                'codn_banco' => 'nullable|integer',
                'codn_sucur' => 'nullable|integer',
                'tipo_cuent' => 'nullable|string|size:2',
                'nro_cuent' => 'nullable|numeric',
                'codn_verif' => 'nullable|integer',
                'nrovalorpago' => 'nullable|integer',
                'cbu' => 'nullable|string|max:25',
            ]);

            $result = $this->service->updateWithTransaction($id, $data);
            if ($result) {
                return response()->json(['message' => 'Registro actualizado con éxito']);
            }
            return response()->json(['message' => 'No se encontró el registro'], 404);
        } catch (Exception $e) {
            return response()->json(['message' => 'Error al actualizar el registro', 'error' => $e->getMessage()], 500);
        }
    }
}
