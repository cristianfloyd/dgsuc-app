<?php

namespace App\Models\Mapuche;

use App\Models\Dh01;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Traits\MapucheConnectionTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use App\Services\Mapuche\PeriodoFiscalService;

class MapucheSicossReporte extends Model
{
    use MapucheConnectionTrait;


    protected $table = 'mapuche.dh21h';
    protected $primaryKey = 'nro_liqui';
    public $timestamps = false;

    /**
     * Obtiene el registro de liquidación asociado con el reporte.
     */
    public function liquidacion()
    {
        return $this->belongsTo(Dh22::class, 'nro_liqui', 'nro_liqui');
    }

    

    /**
     * Scope para obtener el reporte SICOSS.
     *
     * @param Builder $query
     * @param string $anio
     * @param string $mes
     * @return Builder
     */
    public function scopeGetReporte($query, string $anio, string $mes): Builder
    {
        try {
            $tablaPeriodo = $this->determinarTablaPeriodo($anio, $mes);
            $subconsultaLiquidaciones = $this->generarSubconsultaLiquidaciones($anio, $mes);

            return $query->from($tablaPeriodo)
                ->select([
                    "$tablaPeriodo.nro_liqui",
                    'dh22.desc_liqui',
                    DB::connection($this->getConnectionName())->raw('SUM(CASE WHEN codn_conce IN (305) THEN impp_conce * 1 ELSE impp_conce * 0 END)::NUMERIC(15,2) AS c305'),
                    DB::connection($this->getConnectionName())->raw('SUM(CASE WHEN codn_conce IN (306,308) THEN impp_conce * 1 ELSE impp_conce * 0 END)::NUMERIC(15,2) AS c306'),
                    DB::connection($this->getConnectionName())->raw('SUM(CASE WHEN codn_conce IN (201,202,203,205,204) THEN impp_conce * 1 ELSE impp_conce * 0 END)::NUMERIC(15,2) AS aportesijpdh21'),
                    DB::connection($this->getConnectionName())->raw('SUM(CASE WHEN codn_conce IN (247) THEN impp_conce * 1 ELSE impp_conce * 0 END)::NUMERIC(15,2) AS aporteinssjpdh21'),
                    DB::connection($this->getConnectionName())->raw('SUM(CASE WHEN codn_conce IN (301,302,303,304,307) THEN impp_conce * 1 ELSE impp_conce * 0 END)::NUMERIC(15,2) AS contribucionsijpdh21'),
                    DB::connection($this->getConnectionName())->raw('SUM(CASE WHEN codn_conce IN (347) THEN impp_conce * 1 ELSE impp_conce * 0 END)::NUMERIC(15,2) AS contribucioninssjpdh21'),
                    DB::connection($this->getConnectionName())->raw('SUM(CASE WHEN tipo_conce = \'C\' AND nro_orimp != 0 THEN impp_conce ELSE 0 END)::NUMERIC(15,2) AS remunerativo'),
                    DB::connection($this->getConnectionName())->raw('SUM(CASE WHEN tipo_conce = \'S\' THEN impp_conce ELSE 0 END)::NUMERIC(15,2) AS no_remunerativo')
                ])
                ->join('mapuche.dh01', "$tablaPeriodo.nro_legaj", '=', 'dh01.nro_legaj')
                ->join('mapuche.dh22', "$tablaPeriodo.nro_liqui", '=', 'dh22.nro_liqui')
                ->whereIn("$tablaPeriodo.nro_liqui", $subconsultaLiquidaciones)
                ->groupBy("$tablaPeriodo.nro_liqui", 'dh22.desc_liqui');
        } catch (\Exception $e) {
            Log::error('Error en scopeGetReporte', [
                'error' => $e->getMessage(),
                'anio' => $anio,
                'mes' => $mes
            ]);
            return $query->whereRaw('1 = 0');
        }
    }

    /**
     * Scope para obtener los totales del reporte SICOSS.
     *
     * @param Builder $query
     * @param string $anio
     * @param string $mes
     * @return array
     */
    public function scopeGetTotales($query, string $anio, string $mes): array
    {
        try {
            $tablaPeriodo = $this->determinarTablaPeriodo($anio, $mes);
            $subconsultaLiquidaciones = $this->getSubconsultaLiquidaciones($anio, $mes);

            $result = $query->from($tablaPeriodo)
                ->select([
                    DB::connection($this->getConnectionName())->raw('SUM(CASE WHEN codn_conce IN (305) THEN impp_conce * 1 ELSE impp_conce * 0 END)::NUMERIC(15,2) AS total_c305'),
                    DB::connection($this->getConnectionName())->raw('SUM(CASE WHEN codn_conce IN (306) THEN impp_conce * 1 ELSE impp_conce * 0 END)::NUMERIC(15,2) AS total_c306'),
                    DB::connection($this->getConnectionName())->raw('SUM(CASE WHEN codn_conce IN (201,202,203,205,204) THEN impp_conce * 1 ELSE impp_conce * 0 END)::NUMERIC(15,2) as total_aportes_sijp'),
                    DB::connection($this->getConnectionName())->raw('SUM(CASE WHEN codn_conce IN (247) THEN impp_conce * 1 ELSE impp_conce * 0 END)::NUMERIC(15,2) as total_aportes_inssjp'),
                    DB::connection($this->getConnectionName())->raw('SUM(CASE WHEN codn_conce IN (301,302,303,304,307) THEN impp_conce * 1 ELSE impp_conce * 0 END)::NUMERIC(15,2) as total_contribuciones_sijp'),
                    DB::connection($this->getConnectionName())->raw('SUM(CASE WHEN codn_conce IN (347) THEN impp_conce * 1 ELSE impp_conce * 0 END)::NUMERIC(15,2) as total_contribuciones_inssjp'),
                    DB::connection($this->getConnectionName())->raw('SUM(CASE WHEN tipo_conce = \'C\' AND nro_orimp != 0 THEN impp_conce ELSE 0 END)::NUMERIC(15,2) as total_remunerativo'),
                    DB::connection($this->getConnectionName())->raw('SUM(CASE WHEN tipo_conce = \'S\' THEN impp_conce ELSE 0 END)::NUMERIC(15,2) as total_no_remunerativo')
                ])
                ->join('mapuche.dh01', $tablaPeriodo . '.nro_legaj', '=', 'dh01.nro_legaj')
                ->join('mapuche.dh22', $tablaPeriodo . '.nro_liqui', '=', 'dh22.nro_liqui')
                ->whereIn($tablaPeriodo . '.nro_liqui', $subconsultaLiquidaciones)
                ->first();

            // Verificar si hay resultados antes de acceder a las propiedades
            if (!$result) {
                return [
                    'total_aportes' => 0,
                    'total_contribuciones' => 0,
                    'total_remunerativo' => 0,
                    'total_no_remunerativo' => 0,
                    'total_c305' => 0,
                    'total_c306' => 0,
                ];
            }

            return [
                'total_aportes' => $result->total_aportes_sijp + $result->total_aportes_inssjp,
                'total_contribuciones' => $result->total_contribuciones_sijp + $result->total_contribuciones_inssjp,
                'total_remunerativo' => $result->total_remunerativo,
                'total_no_remunerativo' => $result->total_no_remunerativo,
                'total_c305' => $result->total_c305,
                'total_c306' => $result->total_c306,
            ];
        } catch (\Exception $e) {
            Log::error('Error en scopeGetTotales', [
                'error' => $e->getMessage(),
                'anio' => $anio,
                'mes' => $mes
            ]);

            return [
                'total_aportes' => 0,
                'total_contribuciones' => 0,
                'total_remunerativo' => 0,
                'total_no_remunerativo' => 0,
                'total_c305' => 0,
                'total_c306' => 0,
            ];
        }
    }

    /**
     * Determina la tabla de período a utilizar basada en el año y mes proporcionados
     * 
     * @param string $anio Año del período fiscal
     * @param string $mes Mes del período fiscal
     * @return string Nombre de la tabla a utilizar
     */
    private function determinarTablaPeriodo(string $anio, string $mes): string
    {
        try {
            $periodoFiscalService = app(PeriodoFiscalService::class);
            $periodoActual = $periodoFiscalService->getPeriodoFiscalFromDatabase();

            return ((int)$periodoActual['year'] === (int)$anio && (int)$periodoActual['month'] === (int)$mes)
                ? 'mapuche.dh21'
                : 'mapuche.dh21h';
        } catch (\Exception $e) {
            Log::error('Error al determinar tabla de período', [
                'error' => $e->getMessage(),
                'anio' => $anio,
                'mes' => $mes
            ]);
            // En caso de error, usar la tabla histórica por defecto
            return 'mapuche.dh21h';
        }
    }

    /**
     * Genera la subconsulta para filtrar por liquidaciones del período especificado
     * 
     * @param string $anio Año del período fiscal
     * @param string $mes Mes del período fiscal
     * @return \Closure Función que genera la subconsulta
     */
    private function generarSubconsultaLiquidaciones(string $anio, string $mes): \Closure
    {
        return function ($query) use ($anio, $mes) {
            $query->select('nro_liqui')
                ->from('mapuche.dh22')
                ->where('sino_genimp', true)
                ->where('per_liano', $anio)
                ->where('per_limes', $mes);
        };
    }
}
