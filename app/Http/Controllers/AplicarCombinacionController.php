<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ControlDiferencias;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\ControlAportesDiferencia;

class AplicarCombinacionController extends Controller
{
    /**
     * Aplica una combinación seleccionada para resolver una diferencia
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function aplicar(Request $request)
    {
        try {
            // Validar la solicitud
            $request->validate([
                'combinacion' => 'required|array',
                'combinacion.items' => 'required|array',
                'combinacion.total' => 'required|numeric',
                'recordId' => 'required|integer|exists:control_diferencias,id'
            ]);

            // Obtener el registro de control
            $controlDiferencia = ControlAportesDiferencia::findOrFail($request->recordId);

            // Iniciar una transacción para asegurar la integridad de los datos
            DB::beginTransaction();

            // Registrar la combinación aplicada (puedes crear una tabla para esto)
            $combinacionAplicada = [
                'control_diferencia_id' => $controlDiferencia->id,
                'nro_legaj' => $controlDiferencia->nro_legaj,
                'diff_original' => $controlDiferencia->diff_B,
                'combinacion' => json_encode($request->combinacion),
                'total_aplicado' => $request->combinacion['total'],
                'diferencia_resultante' => abs($controlDiferencia->diff_B - $request->combinacion['total']),
                'aplicado_por' => auth()->guard('web')->user()->id,
                'aplicado_en' => now()
            ];

            // Aquí puedes guardar en tu tabla de registro de combinaciones aplicadas
            // DB::table('combinaciones_aplicadas')->insert($combinacionAplicada);

            // Actualizar el registro de control (marcar como resuelto o actualizar la diferencia)
            $controlDiferencia->diff_B = 0; // O la diferencia resultante si prefieres
            $controlDiferencia->estado = 'RESUELTO';
            $controlDiferencia->fecha_resolucion = now();
            $controlDiferencia->save();

            // Registrar los conceptos utilizados para la resolución
            foreach ($request->combinacion['items'] as $item) {
                // Aquí puedes registrar cada concepto utilizado si lo necesitas
                // Por ejemplo, para auditoría o seguimiento
            }

            // Confirmar la transacción
            DB::commit();

            // Registrar la acción en el log
            Log::info('Combinación aplicada correctamente', [
                'usuario' => auth()->guard('web')->user()->name,
                'control_id' => $controlDiferencia->id,
                'nro_legaj' => $controlDiferencia->nro_legaj,
                'combinacion' => $request->combinacion
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Combinación aplicada correctamente',
                'data' => [
                    'control' => $controlDiferencia,
                    'combinacion_aplicada' => $combinacionAplicada
                ]
            ]);

        } catch (\Exception $e) {
            // Revertir la transacción en caso de error
            DB::rollBack();

            Log::error('Error al aplicar combinación: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al aplicar la combinación: ' . $e->getMessage()
            ], 500);
        }
    }
}
