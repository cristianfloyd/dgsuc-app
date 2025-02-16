<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\AfipRelacionesActivas;
use App\Services\Contracts\AfipRelacionesActivasServiceInterface;

class AfipRelacionesActivasService implements AfipRelacionesActivasServiceInterface
{
    public function __construct(
        protected AfipRelacionesActivas $model
    ) {}

    /**
     * Inserta datos masivamente en la tabla
     */
    public function insertarDatosMasivos(array $datosMapeados, int $chunkSize = 1000): bool
    {
        $conexion = 'pgsql-prod';

        DB::connection($conexion)->beginTransaction();

        try {
            foreach (array_chunk($datosMapeados, $chunkSize) as $chunk) {
                $this->model->upsert($chunk, ['cuil'], array_keys($chunk[0]));
            }

            DB::connection($conexion)->commit();
            return true;
        } catch (Exception $e) {
            DB::connection($conexion)->rollBack();
            Log::error('Error al insertar datos masivos', [
                'mensaje' => $e->getMessage(),
                'exception' => $e
            ]);
            return false;
        }
    }

    /**
     * Mapea los datos procesados al modelo
     */
    public function mapearDatosAlModelo(array $datosProcesados): array
    {
        return [
            'periodo_fiscal' => $datosProcesados[0],
            'codigo_movimiento' => $datosProcesados[1],
            'tipo_registro' => $datosProcesados[2],
            'cuil' => $datosProcesados[3],
            'marca_trabajador_agropecuario' => $datosProcesados[4],
            'modalidad_contrato' => $datosProcesados[5],
            'fecha_inicio_relacion_laboral' => $datosProcesados[6],
            'fecha_fin_relacion_laboral' => $datosProcesados[7],
            'codigo_o_social' => $datosProcesados[8],
            'cod_situacion_baja' => $datosProcesados[9],
            'fecha_telegrama_renuncia' => $datosProcesados[10],
            'retribucion_pactada' => $datosProcesados[11],
            'modalidad_liquidacion' => $datosProcesados[12],
            'suc_domicilio_desem' => $datosProcesados[13],
            'actividad_domicilio_desem' => $datosProcesados[14],
            'puesto_desem' => $datosProcesados[15],
            'rectificacion' => $datosProcesados[16],
            'numero_formulario_agro' => $datosProcesados[17],
            'tipo_servicio' => $datosProcesados[18],
            'categoria_profesional' => $datosProcesados[19],
            'ccct' => $datosProcesados[20],
            'no_hay_datos' => $datosProcesados[21]
        ];
    }

    /**
     * Busca registros por CUIL
     */
    public function buscarPorCuil(string $cuil)
    {
        return $this->model->byCuil($cuil)->first();
    }

    /**
     * Obtiene registros por periodo fiscal
     */
    public function obtenerPorPeriodoFiscal(string $periodo)
    {
        return $this->model->where('periodo_fiscal', $periodo)->get();
    }

    /**
     * Obtiene estadísticas por periodo
     */
    public function obtenerEstadisticasPorPeriodo(string $periodo): array
    {
        return [
            'total' => $this->model->where('periodo_fiscal', $periodo)->count(),
            'altas' => $this->model->where('periodo_fiscal', $periodo)
                ->where('codigo_movimiento', '00')
                ->count(),
            'bajas' => $this->model->where('periodo_fiscal', $periodo)
                ->where('codigo_movimiento', '01')
                ->count(),
            'modificaciones' => $this->model->where('periodo_fiscal', $periodo)
                ->where('codigo_movimiento', '02')
                ->count(),
            'retribucion_promedio' => $this->model->where('periodo_fiscal', $periodo)
                ->avg('retribucion_pactada')
        ];
    }
}
