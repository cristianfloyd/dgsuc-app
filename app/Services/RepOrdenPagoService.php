<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Traits\MapucheConnectionTrait;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use App\Models\Reportes\RepOrdenPagoModel;
use App\Repositories\RepOrdenPagoRepository;
use Illuminate\Database\Eloquent\Collection;

/**
 * Proporciona una capa de servicio para administrar registros de RepOrdenPago.
 *
 *
 * Esta clase de servicio proporciona métodos para interactuar con los registros de RepOrdenPago, incluidos:
 *  recuperar todos los registros, recuperar un registro por su nro_liqui, crear un nuevo registro,
 *  actualizar un registro existente y eliminar un registro.
 *
 * La clase de servicio utiliza una clase de repositorio para manejar la lógica de acceso a datos.
 *
 */
class RepOrdenPagoService
{
    use MapucheConnectionTrait;

    /**
     * Crear una nueva instancia
     */
    public function __construct(protected RepOrdenPagoRepository $repository)
    {
    }

    /**
     * Obtiene todos los registros de RepOrdenPago.
     *
     * @return Collection
     */
    public function getAllRepOrdenPago(): Collection
    {
        return $this->repository->getAll();
    }

    /**
     * Get RepOrdenPago by nro_liqui.
     *
     * @param int $nroLiqui
     * @return RepOrdenPagoModel|null
     */
    public function getRepOrdenPagoByNroLiqui(int $nroLiqui): ?RepOrdenPagoModel
    {
        return $this->repository->getByNroLiqui($nroLiqui);
    }

    /**
     * Create a new RepOrdenPago record.
     *
     * @param array $data
     * @return RepOrdenPagoModel
     */
    public function createRepOrdenPago(array $data): RepOrdenPagoModel
    {
        return $this->repository->create($data);
    }

    /**
     * Update an existing RepOrdenPago record.
     *
     * @param RepOrdenPagoModel $repOrdenPago
     * @param array $data
     * @return bool
     */
    public function updateRepOrdenPago(RepOrdenPagoModel $repOrdenPago, array $data): bool
    {
        return $this->repository->update($repOrdenPago, $data);
    }

    /**
     * Delete a RepOrdenPago record.
     *
     * @param RepOrdenPagoModel $repOrdenPago
     * @return bool
     */
    public function deleteRepOrdenPago(RepOrdenPagoModel $repOrdenPago): bool
    {
        return $this->repository->delete($repOrdenPago);
    }

    // Nuevos métodos para gestión de función almacenada
    public function ensureTableAndFunction(): void
    {
        $this->createTableIfNotExists();
        $this->createStoredProcedureIfNotExists();
    }
    private function createTableIfNotExists(): void
    {
        if (!Schema::connection($this->getConnectionName())->hasTable('suc.rep_orden_pago')) {
            Schema::connection($this->getConnectionName())->create('suc.rep_orden_pago', function (Blueprint $table) {
                $table->id();
                $table->integer('nro_liqui')->nullable();
                $table->integer('banco')->nullable();
                $table->string('codn_funci')->nullable();
                $table->string('codn_fuent')->nullable();
                $table->string('codc_uacad')->nullable();
                $table->text('caracter')->nullable();
                $table->string('codn_progr')->nullable();
                $table->decimal('remunerativo', 15, 2)->nullable();
                $table->decimal('no_remunerativo', 15, 2)->nullable();
                $table->decimal('otros_no_remunerativo')->nullable();
                $table->decimal('bruto', 15, 2)->nullable();
                $table->decimal('descuentos', 15, 2)->nullable();
                $table->decimal('aportes', 15, 2)->nullable();
                $table->decimal('sueldo', 15, 2)->nullable();
                $table->decimal('neto', 15, 2)->nullable();
                $table->decimal('estipendio', 15, 2)->nullable();
                $table->decimal('med_resid', 15, 2)->nullable();
                $table->decimal('productividad', 10, 2)->nullable();
                $table->decimal('sal_fam', 10, 2)->nullable();
                $table->decimal('hs_extras', 15, 2)->nullable();
                $table->decimal('total', 15, 2)->nullable();
                $table->decimal('imp_gasto', 15, 2)->nullable();
                $table->timestamps();
            });
            Log::info('Tabla suc.rep_orden_pago creada exitosamente');
        }
    }

    private function createStoredProcedureIfNotExists(): void
    {
        try {
            // Verificar si la función existe
            $functionExists = DB::connection($this->getConnectionName())
                ->select("SELECT EXISTS(SELECT 1 FROM pg_proc WHERE proname = 'rep_orden_pago')");

            if (!$functionExists[0]->exists) {
                DB::connection($this->getConnectionName())->unprepared($this->getStoredProcedureDefinition());
                Log::info('Función rep_orden_pago creada exitosamente');
            }
        } catch (\Exception $e) {
            Log::error('Error al crear función rep_orden_pago: ' . $e->getMessage());
            throw $e;
        }
    }

    public function ensureStoredProcedure(): void
    {
        try {
            $functionExists = DB::connection($this->getConnectionName())
                ->select("SELECT EXISTS(SELECT 1 FROM pg_proc WHERE proname = 'rep_orden_pago')");

            if (!$functionExists[0]->exists) {
                DB::connection($this->getConnectionName())->unprepared($this->getStoredProcedureDefinition());
                Log::info('Función rep_orden_pago creada exitosamente');
            }
        } catch (\Exception $e) {
            Log::error('Error al verificar/crear función rep_orden_pago: ' . $e->getMessage());
            throw $e;
        }
    }

    public function generateReport(array $liquidaciones): void
    {
        try {
            $this->ensureStoredProcedure();

            DB::connection($this->getConnectionName())
                ->statement('SELECT suc.rep_orden_pago(?)', ['{' . implode(',', $liquidaciones) . '}']);

            Log::info('Reporte generado exitosamente para liquidaciones: ' . implode(',', $liquidaciones));
        } catch (\Exception $e) {
            Log::error('Error al generar reporte: ' . $e->getMessage());
            throw $e;
        }
    }

    private function getStoredProcedureDefinition(): string
    {
        return "
        CREATE OR REPLACE FUNCTION suc.rep_orden_pago(p_nro_liqui INTEGER[])
RETURNS void AS $$
BEGIN
    -- Limpiamos los datos existentes para las liquidaciones específicas
    DELETE FROM suc.rep_orden_pago WHERE nro_liqui = ANY(p_nro_liqui);

    -- Insertamos los nuevos datos
    INSERT INTO suc.rep_orden_pago(
		nro_liqui, banco, codn_funci, codn_fuent, codc_uacad, caracter, codn_progr,
        remunerativo, no_remunerativo, bruto, descuentos, aportes,sueldo, estipendio, med_resid,
        productividad, sal_fam, hs_extras, total
	)
    SELECT
    h22.nro_liqui,
	CASE WHEN h92.codn_banco IN (0,1) THEN 0 ELSE 1 END AS banco,
	h21.codn_funci::VARCHAR,
	h21.codn_fuent::VARCHAR,
	h21.codc_uacad::VARCHAR,
	CASE WHEN h03.codc_carac IN ('PERM','PLEN','REGU') THEN 'PERM' ELSE 'CONT' END AS caracter ,  -- personal contratado o permamente
	h21.codn_progr::VARCHAR,
    ROUND(SUM(CASE WHEN h21.tipo_conce= 'C' AND NOT h21.codn_conce IN (121, 122, 124, 125) THEN impp_conce ELSE 0 END)::NUMERIC, 2) AS remunerativo,
    ROUND(SUM(CASE WHEN h21.tipo_conce= 'S' AND NOT h21.codn_conce IN (173, 186) THEN impp_conce ELSE 0 END)::NUMERIC, 2) AS no_remunerativo,
    ROUND((
        ROUND(SUM(CASE WHEN h21.tipo_conce= 'C' AND NOT h21.codn_conce IN (121, 122, 124, 125) THEN impp_conce ELSE 0 END)::NUMERIC, 2) +
        ROUND(SUM(CASE WHEN h21.tipo_conce= 'S' AND NOT h21.codn_conce IN (173, 186) THEN impp_conce ELSE 0 END)::NUMERIC, 2)
    )::NUMERIC,2) AS bruto,
    ROUND(SUM(CASE WHEN h21.tipo_conce= 'D' AND h21.codn_conce/100 = 2 THEN impp_conce ELSE 0 END)::NUMERIC, 2) AS descuentos,
    ROUND(SUM(CASE WHEN h21.tipo_conce= 'A' THEN impp_conce ELSE 0 END)::NUMERIC, 2) AS aportes,
	ROUND((
		ROUND(SUM(CASE WHEN h21.tipo_conce= 'C' AND NOT h21.codn_conce IN (121, 122, 124, 125) THEN impp_conce ELSE 0 END)::NUMERIC, 2)
		+ ROUND(SUM(CASE WHEN h21.tipo_conce= 'S' AND NOT h21.codn_conce IN (173, 186) THEN impp_conce ELSE 0 END)::NUMERIC, 2)
		- ROUND(SUM(CASE WHEN h21.tipo_conce= 'D' AND h21.codn_conce/100 = 2 THEN impp_conce ELSE 0 END)::NUMERIC, 2)
	)::NUMERIC, 2) AS sueldo,
    ROUND(SUM(CASE WHEN h21.codn_conce IN (173) THEN impp_conce ELSE 0 END)::NUMERIC, 2) as estipendio,
	ROUND(SUM(CASE WHEN h21.codn_conce IN (186) THEN impp_conce ELSE 0 END)::NUMERIC, 2) as med_resid,
	0::NUMERIC(10,2) as productividad,
	0::NUMERIC(10,2) as sal_fam,
    ROUND(SUM(CASE WHEN h21.codn_conce IN (121, 122, 124, 125) THEN impp_conce ELSE 0 END)::NUMERIC, 2) AS hs_extras,
    ROUND((
    ROUND(SUM(CASE WHEN h21.tipo_conce= 'C' AND NOT h21.codn_conce IN (121, 122, 124, 125) THEN impp_conce ELSE 0 END)::NUMERIC, 2)
        + ROUND(SUM(CASE WHEN h21.tipo_conce= 'S' AND NOT h21.codn_conce IN (173, 186) THEN impp_conce ELSE 0 END)::NUMERIC, 2)
	    - ROUND(SUM(CASE WHEN h21.tipo_conce= 'D' AND h21.codn_conce/100 = 2 THEN impp_conce ELSE 0 END)::NUMERIC, 2)
	    + ROUND(SUM(CASE WHEN h21.codn_conce IN (173) THEN impp_conce ELSE 0 END)::NUMERIC, 2)
	    + ROUND(SUM(CASE WHEN h21.codn_conce IN (186) THEN impp_conce ELSE 0 END)::NUMERIC, 2)
        + 0
		+ 0
        + SUM(CASE WHEN h21.codn_conce IN (121, 122, 124, 125) THEN impp_conce ELSE 0 END)
    )::NUMERIC, 2) AS total
FROM
	mapuche.dh21 h21
	JOIN mapuche.dh22 h22 ON h21.nro_liqui=h22.nro_liqui
	JOIN mapuche.dh03 h03 ON h21.nro_cargo=h03.nro_cargo
	JOIN mapuche.dh12 h12 ON h21.codn_conce=h12.codn_conce
	LEFT JOIN mapuche.dh92 h92 ON h21.nro_legaj=h92.nrolegajo
WHERE h21.nro_liqui = ANY(p_nro_liqui)
GROUP BY
  h22.nro_liqui,
	banco,
	h21.codn_funci,
	h21.codn_fuent,
	h21.codc_uacad,
	caracter,
	h21.codn_progr
ORDER BY
  h22.nro_liqui,
  banco desc,
	h21.codn_funci,
	h21.codn_fuent,
	h21.codc_uacad,
	caracter,
	h21.codn_progr;
END;
$$ LANGUAGE plpgsql;";
    }
}
