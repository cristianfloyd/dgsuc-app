<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Traits\MapucheConnectionTrait;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use App\Models\Reportes\RepOrdenPagoModel;
use Illuminate\Database\Eloquent\Collection;
use App\Contracts\RepOrdenPagoRepositoryInterface;

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
    public function __construct(protected RepOrdenPagoRepositoryInterface $repository)
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
                    $table->integer('nro_liqui');
                    $table->integer('banco')->nullable();
                    $table->integer('codn_funci')->nullable();
                    $table->integer('codn_fuent')->nullable();
                    $table->char('codc_uacad', 4)->nullable();
                    $table->char('caracter', 4)->nullable();
                    $table->integer('codn_progr')->nullable();
                    $table->decimal('remunerativo',  15,  2)->default(0.00);
                    $table->decimal('no_remunerativo', 15, 2)->default(0.00);
                    $table->decimal('otros_no_remunerativo', 15, 2)->default(0.00);
                    $table->decimal('bruto', 15, 2)->default(0.00);
                    $table->decimal('descuentos', 15, 2)->default(0.00);
                    $table->decimal('aportes', 15, 2)->default(0.00);
                    $table->decimal('sueldo', 15, 2)->default(0.00);
                    $table->decimal('neto', 15, 2)->default(0.00);
                    $table->decimal('estipendio', 15, 2)->default(0.00);
                    $table->decimal('med_resid', 15, 2)->default(0.00);
                    $table->decimal('productividad', 15, 2)->default(0.00);
                    $table->decimal('sal_fam', 15, 2)->default(0.00);
                    $table->decimal('hs_extras', 15, 2)->default(0.00);
                    $table->decimal('total', 15, 2)->default(0.00);
                    $table->decimal('imp_gasto', 15, 2)->default(0.00);
                    $table->timestamps();

                    // Índices
                    $table->index('nro_liqui');
                    $table->index('codc_uacad');
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
	nro_liqui, banco, codn_funci, codn_fuent, codc_uacad, caracter, codn_progr, sueldo,
   remunerativo, no_remunerativo, otros_no_remunerativo, hs_extras, estipendio, med_resid,
   aportes, descuentos, neto, productividad, sal_fam, imp_gasto
)
SELECT h22.nro_liqui,
       CASE WHEN h92.codn_banco IN ( 0, 1 ) THEN 0 ELSE 1 END                                                                                                                 AS banco,
       h21.codn_funci,
       h21.codn_fuent,
       h21.codc_uacad,
       CASE WHEN h03.codc_carac IN ( 'PERM', 'PLEN', 'REGU' ) THEN 'PERM' ELSE 'CONT' END                                                    AS caracter, -- personal contratado o permamente
       h21.codn_progr,
		(SUM(CASE WHEN h21.tipo_conce= 'C' AND h21.nro_orimp != 0 AND NOT h21.codn_conce IN (121, 122, 124, 125) THEN impp_conce ELSE 0 END) +
		 SUM(CASE WHEN h21.tipo_conce= 'S' AND h21.nro_orimp != 0 AND NOT h21.codn_conce IN (173, 186) THEN impp_conce ELSE 0 END) -
		 SUM(CASE WHEN h21.tipo_conce= 'D' AND h21.nro_orimp != 0 AND h21.codn_conce/100 = 2 THEN impp_conce ELSE 0 END))::NUMERIC(15, 2)     AS sueldo,
        SUM( CASE WHEN h21.tipo_conce = 'C' AND h21.nro_orimp != 0 AND NOT h21.codn_conce IN ( 121, 122, 124, 125 ) THEN impp_conce ELSE 0 END )::NUMERIC(15,2)      AS remunerativo,
        SUM( CASE WHEN h21.tipo_conce = 'S' AND NOT h21.codn_conce IN ( 173, 186 ) THEN impp_conce ELSE 0 END )::NUMERIC(15,2)                AS no_remunerativo,
        SUM( CASE WHEN h21.tipo_conce = 'O' AND h21.nro_orimp != 0 THEN impp_conce ELSE 0 END )::NUMERIC(15,2)                                AS otros_no_remunerativo,
        SUM( CASE WHEN h21.tipo_conce = 'C' AND h21.codn_conce IN ( 121, 122, 124, 125 ) THEN impp_conce ELSE 0 END )::NUMERIC(15, 2)         AS hs_extras,
        SUM( CASE WHEN h21.tipo_conce = 'S' AND h21.codn_conce IN (173) THEN impp_conce ELSE 0 END )::NUMERIC(15,2)                           AS estipendio,
        SUM( CASE WHEN h21.tipo_conce = 'S' AND h21.codn_conce IN (186) THEN impp_conce ELSE 0 END )::NUMERIC(15,2)                           AS med_resid,
        SUM( CASE WHEN h21.tipo_conce = 'A' THEN impp_conce ELSE 0 END )::NUMERIC(15,2)                                                       AS aportes,
        SUM( CASE WHEN h21.tipo_conce = 'D' AND h21.codn_conce/100=2 THEN impp_conce ELSE 0 END )::NUMERIC(15,2)                                AS descuentos,

		SUM( CASE WHEN h21.tipo_conce = 'C' AND h21.nro_orimp != 0 AND NOT h21.codn_conce IN ( 121, 122, 124, 125 ) THEN impp_conce ELSE 0 END )  +
		SUM( CASE WHEN h21.tipo_conce = 'C' AND h21.codn_conce IN ( 121, 122, 124, 125 ) THEN impp_conce ELSE 0 END ) +
		SUM( CASE WHEN h21.tipo_conce = 'O' AND h21.nro_orimp != 0 THEN impp_conce ELSE 0 END )::NUMERIC(15,2) +
		SUM( CASE WHEN h21.tipo_conce = 'S' AND h12.nro_orimp > 0 AND NOT h21.codn_conce IN ( 173, 186 ) THEN impp_conce ELSE 0 END ) +
		SUM( CASE WHEN h21.tipo_conce = 'S' AND h21.codn_conce IN (173) THEN impp_conce ELSE 0 END ) +
		SUM( CASE WHEN h21.tipo_conce = 'S' AND h21.codn_conce IN (186) THEN impp_conce ELSE 0 END ) -
		SUM( CASE WHEN h21.tipo_conce = 'D' AND h21.codn_conce/100=2 THEN impp_conce ELSE 0 END )                     AS neto,

       0::NUMERIC(10, 2)                                                                                                                      AS productividad,
       0::NUMERIC(10, 2)                                                                                                                      AS sal_fam,


		 SUM( CASE WHEN h21.tipo_conce = 'C' AND h21.nro_orimp != 0 AND NOT h21.codn_conce IN ( 121, 122, 124, 125 ) THEN impp_conce ELSE 0 END ) +
		 SUM( CASE WHEN h21.tipo_conce = 'C' AND h21.codn_conce IN ( 121, 122, 124, 125 ) THEN impp_conce ELSE 0 END )  +
		 SUM( CASE WHEN h21.tipo_conce = 'O' AND h21.nro_orimp != 0 THEN impp_conce ELSE 0 END )::NUMERIC(15,2) +
		 SUM( CASE WHEN h21.tipo_conce = 'S' AND NOT h21.codn_conce IN ( 173, 186 ) THEN impp_conce ELSE 0 END ) +
		 SUM( CASE WHEN h21.tipo_conce = 'S' AND h21.codn_conce IN (173) THEN impp_conce ELSE 0 END ) +
		 SUM( CASE WHEN h21.tipo_conce = 'S' AND h21.codn_conce IN (186) THEN impp_conce ELSE 0 END ) +
		 0 +
		 0 +
		 SUM( CASE WHEN h21.tipo_conce = 'A' AND h21.nro_orimp != 0 THEN impp_conce ELSE 0 END )
		     AS imp_gasto
--INTO TABLE suc.rep_orden_pago
FROM mapuche.dh21 h21
	JOIN mapuche.dh22 h22 ON h21.nro_liqui = h22.nro_liqui
	JOIN mapuche.dh03 h03 ON h21.nro_cargo = h03.nro_cargo
 	LEFT JOIN mapuche.dh17 ON (h21.codn_conce = dh17.codn_conce)
	JOIN mapuche.dh12 h12 ON h21.codn_conce = h12.codn_conce
	LEFT JOIN mapuche.dh92 h92 ON h21.nro_legaj = h92.nrolegajo
WHERE h21.nro_liqui = ANY(p_nro_liqui)
--     AND h21.nro_legaj = 249616
GROUP BY h22.nro_liqui, banco, h21.codn_funci, h21.codn_fuent, h21.codc_uacad, caracter, h21.codn_progr
ORDER BY h22.nro_liqui, banco DESC, h21.codn_funci, h21.codn_fuent, h21.codc_uacad, caracter, h21.codn_progr;
END;
$$ LANGUAGE plpgsql;";
    }

    /**
     * Trunca la tabla rep_orden_pago
     *
     * @return bool
     * @throws \Exception
     */
    public function truncateTable(): bool
    {
        try {
            $result = $this->repository->truncate();
            Log::info('Tabla suc.rep_orden_pago truncada exitosamente');
            return $result;
        } catch (\Exception $e) {
            Log::error('Error al truncar tabla suc.rep_orden_pago: ' . $e->getMessage());
            throw $e;
        }
    }
}
