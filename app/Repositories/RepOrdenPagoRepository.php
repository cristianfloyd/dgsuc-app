<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Traits\MapucheConnectionTrait;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use App\Models\Reportes\RepOrdenPagoModel;
use Illuminate\Database\Eloquent\Collection;
use App\Contracts\RepOrdenPagoRepositoryInterface;

class RepOrdenPagoRepository implements RepOrdenPagoRepositoryInterface
{
    use MapucheConnectionTrait;


    public function __construct(protected RepOrdenPagoModel $model)
    {}
    /**
     * Obtiene todas las instancias de RepOrdenPagoModel.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAll(array|int|null $nroLiquis = null): Collection
    {
        $query = RepOrdenPagoModel::query()
            ->orderBy('banco', 'desc')
            ->orderBy('codn_funci', 'asc')
            ->orderBy('codn_fuent', 'asc')
            ->orderBy('codc_uacad', 'asc');

        if (is_array($nroLiquis)) {
            $query->whereIn(column: 'nro_liqui', values: $nroLiquis);
        } elseif (is_int(value: $nroLiquis)) {
            $query->where(column: 'nro_liqui', operator: $nroLiquis);
        }

        return $query->get();
    }

    /**
     * Obtiene todas las instancias de RepOrdenPagoModel junto con su unidad académica.
     *
     * @param array|int|null $nroLiquis Un arreglo de números de liquidación o un número de liquidación individual para filtrar los resultados. Si se omite, se devolverán todos los registros.
     * @return Collection La colección de instancias de RepOrdenPagoModel con su unidad académica.
     */
    public function getAllWithUnidadAcademica(array|int|null $nroLiquis = null): Collection
    {
        $query = RepOrdenPagoModel::with(relations: ['unidadAcademica' => function ($query): void {
            $query->select('nro_tabla', 'desc_abrev', 'desc_item');
        }])
        ->orderBy('banco', 'desc')
        ->orderBy('codn_funci', 'asc')
        ->orderBy('codn_fuent', 'asc')
        ->orderBy('codc_uacad', 'asc');

        if (is_array($nroLiquis)) {
            $query->whereIn(column: 'nro_liqui', values: $nroLiquis);
        } elseif (is_int(value: $nroLiquis)) {
            $query->where(column: 'nro_liqui', operator: $nroLiquis);
        }

        return $query->get();
    }

    /**
     * Obtiene la primera instancia de RepOrdenPagoModel que coincida con el número de liquidación proporcionado.
     *
     * @param int $nroLiqui El número de liquidación a buscar.
     * @return RepOrdenPagoModel|null La primera instancia de RepOrdenPagoModel que coincida con el número de liquidación, o null si no se encuentra ninguna.
     */
    public function getByNroLiqui(int $nroLiqui): ?RepOrdenPagoModel
    {
        return RepOrdenPagoModel::where('nro_liqui', $nroLiqui)->first();
    }


    /**
     * Crea una nueva instancia de RepOrdenPagoModel con los datos proporcionados.
     *
     * @param array $data Los datos para crear la nueva instancia.
     * @return RepOrdenPagoModel La nueva instancia creada.
     */
    public function create(array $data): RepOrdenPagoModel
    {
        return RepOrdenPagoModel::create($data);
    }


    /**
     * Actualiza los datos de una instancia de RepOrdenPagoModel.
     *
     * @param RepOrdenPagoModel $repOrdenPago La instancia de RepOrdenPagoModel a actualizar.
     * @param array $data Los nuevos datos para actualizar la instancia.
     * @return bool Verdadero si la actualización fue exitosa, falso en caso contrario.
     */
    public function update(RepOrdenPagoModel $repOrdenPago, array $data): bool
    {
        return $repOrdenPago->update($data);
    }

    /**
     * @inheritDoc
     */
    public function delete(RepOrdenPagoModel $repOrdenPago): bool
    {
        return $repOrdenPago->delete();
    }

    /**
     * Trunca la tabla rep_orden_pago
     *
     * @return bool
     * @throws \Exception
     */
    public function truncate(): bool
    {
        try {
            DB::connection($this->model->getConnectionName())->beginTransaction();

            $result = DB::connection($this->model->getConnectionName())
                ->statement('TRUNCATE TABLE suc.rep_orden_pago RESTART IDENTITY CASCADE');

            DB::connection($this->model->getConnectionName())->commit();

            return $result;
        } catch (\Exception $e) {
            DB::connection($this->model->getConnectionName())->rollBack();
            throw $e;
        }
    }

    /**
     * @inheritDoc
     */
    public function createTableIfNotExists(): void
    {
        try {
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
        } catch (\Throwable $e) {
            Log::error('Error al crear tabla suc.rep_orden_pago: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Verifica si existe el procedimiento almacenado rep_orden_pago y lo crea si no existe.
     *
     * @return void
     * @throws \Exception
     */
    public function ensureStoredProcedure(): void
    {
        try {
            $functionExists = DB::connection($this->model->getConnectionName())
                ->select("SELECT EXISTS(SELECT 1 FROM pg_proc WHERE proname = 'rep_orden_pago')");

            if (!$functionExists[0]->exists) {
                DB::connection($this->model->getConnectionName())->unprepared($this->getStoredProcedureDefinition());
                Log::info('Función rep_orden_pago creada exitosamente');
            }
        } catch (\Exception $e) {
            Log::error('Error al verificar/crear función rep_orden_pago: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * @inheritDoc
     */
    public function executeStoredProcedure(array $liquidaciones): void
    {
        try {
            // Asegurarse de que el procedimiento almacenado existe
            $this->ensureStoredProcedure();
            
            // Ejecutar el procedimiento almacenado
            DB::connection($this->model->getConnectionName())
                ->statement('SELECT suc.rep_orden_pago(?)', ['{' . implode(',', $liquidaciones) . '}']);
                
            Log::info('Reporte generado exitosamente para liquidaciones: ' . implode(',', $liquidaciones));
        } catch (\Exception $e) {
            Log::error('Error al ejecutar procedimiento almacenado rep_orden_pago: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * @inheritDoc
     */
    public function getStoredProcedureDefinition(): string
    {
        return "
    CREATE FUNCTION rep_orden_pago(p_nro_liqui integer[]) RETURNS void
    LANGUAGE plpgsql AS
    $$
    BEGIN
        -- Limpiamos los datos existentes para las liquidaciones específicas
        DELETE FROM suc.rep_orden_pago WHERE nro_liqui = ANY(p_nro_liqui);
        
        -- Insertamos los nuevos datos
    INSERT INTO suc.rep_orden_pago(
        nro_liqui, banco, codn_funci, codn_fuent, codc_uacad, caracter, codn_progr, sueldo,
        remunerativo, no_remunerativo, otros_no_remunerativo, hs_extras, estipendio, med_resid,
        aportes, descuentos, neto, productividad, sal_fam, total, imp_gasto
    )
    SELECT h22.nro_liqui,
            CASE WHEN h92.codn_banco IN ( 0, 1 ) THEN 0 ELSE 1 END                                                                                                        AS banco,
            h21.codn_funci,
            h21.codn_fuent,
            h21.codc_uacad,
            CASE WHEN h03.codc_carac IN ( 'PERM', 'PLEN', 'REGU' ) THEN 'PERM' ELSE 'CONT' END                                                                            AS caracter, -- personal contratado o permamente
            h21.codn_progr,
            (SUM(CASE WHEN h21.tipo_conce= 'C' AND h21.nro_orimp != 0 AND NOT h21.codn_conce IN (121, 122, 124, 125) THEN impp_conce ELSE 0 END) +
            SUM(CASE WHEN h21.tipo_conce= 'S' AND h21.nro_orimp != 0 AND NOT h21.codn_conce IN (173, 186) THEN impp_conce ELSE 0 END) -
            SUM(CASE WHEN h21.tipo_conce= 'D' AND h21.nro_orimp != 0 AND h21.codn_conce/100 = 2 THEN impp_conce ELSE 0 END))::NUMERIC(15, 2)                              AS sueldo,
            SUM( CASE WHEN h21.tipo_conce = 'C' AND h21.nro_orimp != 0 AND NOT h21.codn_conce IN ( 121, 122, 124, 125 ) THEN impp_conce ELSE 0 END )::NUMERIC(15,2)      AS remunerativo,
            SUM( CASE WHEN h21.tipo_conce = 'S' AND h21.nro_orimp != 0 AND NOT h21.codn_conce IN ( 173, 186 ) THEN impp_conce ELSE 0 END )::NUMERIC(15,2)                AS no_remunerativo,
            SUM( CASE WHEN h21.tipo_conce = 'O' AND h21.nro_orimp != 0 THEN impp_conce ELSE 0 END )::NUMERIC(15,2)                                                       AS otros_no_remunerativo,
            SUM( CASE WHEN h21.tipo_conce = 'C' AND h21.codn_conce IN ( 121, 122, 124, 125 ) THEN impp_conce ELSE 0 END )::NUMERIC(15, 2)                                AS hs_extras,
            SUM( CASE WHEN h21.tipo_conce = 'S' AND h21.nro_orimp != 0 AND h21.codn_conce IN (173) THEN impp_conce ELSE 0 END )::NUMERIC(15,2)                           AS estipendio,
            SUM( CASE WHEN h21.tipo_conce = 'S' AND h21.nro_orimp != 0 AND h21.codn_conce IN (186) THEN impp_conce ELSE 0 END )::NUMERIC(15,2)                           AS med_resid,
            SUM( CASE WHEN h21.tipo_conce = 'A' AND h21.nro_orimp != 0 THEN impp_conce ELSE 0 END )::NUMERIC(15,2)                                                       AS aportes,
            SUM( CASE WHEN h21.tipo_conce = 'D' AND h21.nro_orimp != 0 AND h21.codn_conce/100=2 THEN impp_conce ELSE 0 END )::NUMERIC(15,2)                              AS descuentos,
        
            SUM( CASE WHEN h21.tipo_conce = 'C' AND h21.nro_orimp != 0 AND NOT h21.codn_conce IN ( 121, 122, 124, 125 ) THEN impp_conce ELSE 0 END )  +
            SUM( CASE WHEN h21.tipo_conce = 'C' AND h21.nro_orimp != 0 AND h21.codn_conce IN ( 121, 122, 124, 125 ) THEN impp_conce ELSE 0 END ) +
            SUM( CASE WHEN h21.tipo_conce = 'O' AND h21.nro_orimp != 0 THEN impp_conce ELSE 0 END )::NUMERIC(15,2) +
            SUM( CASE WHEN h21.tipo_conce = 'S' AND h12.nro_orimp > 0 AND NOT h21.codn_conce IN ( 173, 186 ) THEN impp_conce ELSE 0 END ) +
            SUM( CASE WHEN h21.tipo_conce = 'S' AND h21.nro_orimp != 0 AND h21.codn_conce IN (173) THEN impp_conce ELSE 0 END ) +
            SUM( CASE WHEN h21.tipo_conce = 'S' AND h21.nro_orimp != 0 AND h21.codn_conce IN (186) THEN impp_conce ELSE 0 END ) -
            SUM( CASE WHEN h21.tipo_conce = 'D' AND h21.nro_orimp != 0 AND h21.codn_conce/100=2 THEN impp_conce ELSE 0 END )                                               AS neto,
        
            0::NUMERIC(10, 2)                                                                                                          AS productividad,
            0::NUMERIC(10, 2)                                                                                                          AS sal_fam,
        
            SUM( CASE WHEN h21.tipo_conce = 'C' AND h21.nro_orimp != 0 AND NOT h21.codn_conce IN ( 121, 122, 124, 125 ) THEN impp_conce ELSE 0 END ) +
    		SUM( CASE WHEN h21.tipo_conce = 'C' AND h21.codn_conce IN ( 121, 122, 124, 125 ) THEN impp_conce ELSE 0 END )  +
    		SUM( CASE WHEN h21.tipo_conce = 'O' AND h21.nro_orimp != 0 THEN impp_conce ELSE 0 END )::NUMERIC(15,2) +
    		SUM( CASE WHEN h21.tipo_conce = 'S' AND h21.nro_orimp != 0 AND NOT h21.codn_conce IN ( 173, 186 ) THEN impp_conce ELSE 0 END ) +
    		SUM( CASE WHEN h21.tipo_conce = 'S' AND h21.codn_conce IN (173) THEN impp_conce ELSE 0 END ) +
    		SUM( CASE WHEN h21.tipo_conce = 'S' AND h21.codn_conce IN (186) THEN impp_conce ELSE 0 END ) +
    		0 +
    		0 +
    		SUM( CASE WHEN h21.tipo_conce = 'A' AND h21.nro_orimp != 0 THEN impp_conce ELSE 0 END )                                               AS total,
        
    		SUM( CASE WHEN h21.tipo_conce = 'C' AND h21.nro_orimp != 0 AND NOT h21.codn_conce IN ( 121, 122, 124, 125 ) THEN impp_conce ELSE 0 END ) +
    		SUM( CASE WHEN h21.tipo_conce = 'C' AND h21.codn_conce IN ( 121, 122, 124, 125 ) THEN impp_conce ELSE 0 END )  +
    		SUM( CASE WHEN h21.tipo_conce = 'O' AND h21.nro_orimp != 0 THEN impp_conce ELSE 0 END )::NUMERIC(15,2) +
    		SUM( CASE WHEN h21.tipo_conce = 'S' AND h21.nro_orimp != 0 AND NOT h21.codn_conce IN ( 173, 186 ) THEN impp_conce ELSE 0 END ) +
    		SUM( CASE WHEN h21.tipo_conce = 'S' AND h21.codn_conce IN (173) THEN impp_conce ELSE 0 END ) +
    		SUM( CASE WHEN h21.tipo_conce = 'S' AND h21.codn_conce IN (186) THEN impp_conce ELSE 0 END ) +
    		0 +
    		0 +
    		SUM( CASE WHEN h21.tipo_conce = 'A' AND h21.nro_orimp != 0 THEN impp_conce ELSE 0 END )                                               AS imp_gasto
        
    FROM (
        SELECT * FROM mapuche.dh21
        UNION ALL
        SELECT * FROM mapuche.dh21h
    ) h21
    	JOIN mapuche.dh22 h22 ON h21.nro_liqui = h22.nro_liqui
    	JOIN mapuche.dh03 h03 ON h21.nro_cargo = h03.nro_cargo
     	LEFT JOIN mapuche.dh17 ON (h21.codn_conce = dh17.codn_conce)
    	JOIN mapuche.dh12 h12 ON h21.codn_conce = h12.codn_conce
    	LEFT JOIN mapuche.dh92 h92 ON h21.nro_legaj = h92.nrolegajo
    WHERE h21.nro_liqui = ANY(p_nro_liqui)
    GROUP BY h22.nro_liqui, banco, h21.codn_funci, h21.codn_fuent, h21.codc_uacad, caracter, h21.codn_progr
    ORDER BY h22.nro_liqui, banco DESC, h21.codn_funci, h21.codn_fuent, h21.codc_uacad, caracter, h21.codn_progr;
    END;
    $$;";
    }
}
