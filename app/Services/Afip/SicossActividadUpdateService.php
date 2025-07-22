<?php

namespace App\Services\Afip;

use App\Traits\MapucheConnectionTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SicossActividadUpdateService
{
    use MapucheConnectionTrait;

    /**
     * Create a new class instance.
     */
    public function __construct()
    {

    }

    /**
     * Actualiza el código de actividad (cod_act) en la tabla afip_mapuche_sicoss.
     *
     * Este método actualiza el campo cod_act en la tabla afip_mapuche_sicoss tomando los valores
     * del campo codigoactividad de la tabla mapuche.dha8, relacionando los registros a través
     * del CUIL del empleado. Solo se actualizan los registros donde el código de actividad difiere.
     *
     *
     * @throws \Throwable Si ocurre algún error durante la transacción
     *
     * @return array Retorna un array con el estado de la operación:
     *               - ['status' => 'success', 'message' => string] en caso de éxito
     *               - ['status' => 'error', 'message' => string] en caso de error
     */
    public function actualizarCodAct(): array
    {
        try {
            DB::connection($this->getConnectionName())->beginTransaction();

            $sql = '
                UPDATE suc.afip_mapuche_sicoss
                SET cod_act = a.codigoactividad
                FROM mapuche.dha8 a
                JOIN vdh01 c ON a.nro_legajo = c.nro_legaj
                WHERE suc.afip_mapuche_sicoss.cuil = c.cuil
                  AND a.codigoactividad <> suc.afip_mapuche_sicoss.cod_act
            ';

            $affected = DB::connection($this->getConnectionName())->update($sql);

            DB::connection($this->getConnectionName())->commit();

            return [
                'status' => 'success',
                'message' => "Se actualizaron $affected registros de cod_act.",
            ];
        } catch (\Throwable $e) {
            DB::connection($this->getConnectionName())->rollBack();
            Log::error('Error en actualización de cod_act', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return [
                'status' => 'error',
                'message' => $e->getMessage(),
            ];
        }
    }
}
