<?php

namespace App\Models\Mapuche;

use App\Models\Dh01;
use Illuminate\Support\Facades\DB;
use App\Traits\MapucheConnectionTrait;
use Illuminate\Database\Eloquent\Model;
use App\Services\Mapuche\PeriodoFiscalService;
use Illuminate\Database\Eloquent\Builder;

class MapucheSicossReporte extends Model
{
    use MapucheConnectionTrait;

    /**
     * The table associated with the model.
     * @var string
     */
    protected $table = 'mapuche.dh21h';

    /**
     * The primary key for the model.
     * @var string
     */
    protected $primaryKey = 'nro_liqui';

    /**
     * Indicates if the model should be timestamped.
     * @var bool
     */
    public $timestamps = false;

    /**
     * The connection name for the model.
     * @var string
     */
    protected $connection = 'mapuche';

    /**
     * Get the liquidación record associated with the reporte.
     */
    public function liquidacion()
    {
        return $this->belongsTo(Dh22::class, 'nro_liqui', 'nro_liqui');
    }

    /**
     * Get the empleado record associated with the reporte.
     */
    public function empleado()
    {
        return $this->belongsTo(Dh01::class, 'nro_legaj', 'nro_legaj');
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
        $periodoFiscalService = app(PeriodoFiscalService::class);
        $periodoActual = $periodoFiscalService->getPeriodoFiscalFromDatabase();

        $tablaPeriodo = ((int)$periodoActual['year'] === (int)$anio && (int)$periodoActual['month'] === (int)$mes)
            ? 'mapuche.dh21'
            : 'mapuche.dh21h';

        return $query->from($tablaPeriodo)
            ->select([
                $tablaPeriodo . '.nro_liqui',
                'dh22.desc_liqui',
                DB::raw('SUM(CASE WHEN codn_conce IN (201,202,203,205,204) THEN impp_conce * 1 ELSE impp_conce * 0 END)::NUMERIC(15,2) AS aportesijpdh21'),
                DB::raw('SUM(CASE WHEN codn_conce IN (247) THEN impp_conce * 1 ELSE impp_conce * 0 END)::NUMERIC(15,2) AS aporteinssjpdh21'),
                DB::raw('SUM(CASE WHEN codn_conce IN (301,302,303,304,307) THEN impp_conce * 1 ELSE impp_conce * 0 END)::NUMERIC(15,2) AS contribucionsijpdh21'),
                DB::raw('SUM(CASE WHEN codn_conce IN (347) THEN impp_conce * 1 ELSE impp_conce * 0 END)::NUMERIC(15,2) AS contribucioninssjpdh21'),
                DB::raw('SUM(CASE WHEN tipo_conce = \'C\' AND nro_orimp != 0 THEN impp_conce ELSE 0 END)::NUMERIC(15,2) AS remunerativo'),
                DB::raw('SUM(CASE WHEN tipo_conce = \'S\' THEN impp_conce ELSE 0 END)::NUMERIC(15,2) AS no_remunerativo')
            ])
            ->join('mapuche.dh01', $tablaPeriodo . '.nro_legaj', '=', 'dh01.nro_legaj')
            ->join('mapuche.dh22', $tablaPeriodo . '.nro_liqui', '=', 'dh22.nro_liqui')
            ->whereIn($tablaPeriodo . '.nro_liqui', function ($query) use ($anio, $mes) {
                $query->select('nro_liqui')
                    ->from('mapuche.dh22')
                    ->where('sino_genimp', true)
                    ->where('per_liano', $anio)
                    ->where('per_limes', $mes);
            })
            ->groupBy($tablaPeriodo . '.nro_liqui', 'dh22.desc_liqui');
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
        $periodoFiscalService = app(PeriodoFiscalService::class);
        $periodoActual = $periodoFiscalService->getPeriodoFiscalFromDatabase();

        $tablaPeriodo = ((int)$periodoActual['year'] === (int)$anio && (int)$periodoActual['month'] === (int)$mes)
            ? 'mapuche.dh21'
            : 'mapuche.dh21h';

        $result = $query->from($tablaPeriodo)
            ->select([
                DB::raw('SUM(CASE WHEN codn_conce IN (201,202,203,205,204) THEN impp_conce * 1 ELSE impp_conce * 0 END)::NUMERIC(15,2) as total_aportes_sijp'),
                DB::raw('SUM(CASE WHEN codn_conce IN (247) THEN impp_conce * 1 ELSE impp_conce * 0 END)::NUMERIC(15,2) as total_aportes_inssjp'),
                DB::raw('SUM(CASE WHEN codn_conce IN (301,302,303,304,307) THEN impp_conce * 1 ELSE impp_conce * 0 END)::NUMERIC(15,2) as total_contribuciones_sijp'),
                DB::raw('SUM(CASE WHEN codn_conce IN (347) THEN impp_conce * 1 ELSE impp_conce * 0 END)::NUMERIC(15,2) as total_contribuciones_inssjp'),
                DB::raw('SUM(CASE WHEN tipo_conce = \'C\' AND nro_orimp != 0 THEN impp_conce ELSE 0 END)::NUMERIC(15,2) as total_remunerativo'),
                DB::raw('SUM(CASE WHEN tipo_conce = \'S\' THEN impp_conce ELSE 0 END)::NUMERIC(15,2) as total_no_remunerativo')
            ])
            ->join('mapuche.dh01', $tablaPeriodo . '.nro_legaj', '=', 'dh01.nro_legaj')
            ->join('mapuche.dh22', $tablaPeriodo . '.nro_liqui', '=', 'dh22.nro_liqui')
            ->whereIn($tablaPeriodo . '.nro_liqui', function ($query) use ($anio, $mes) {
                $query->select('nro_liqui')
                    ->from('mapuche.dh22')
                    ->where('sino_genimp', true)
                    ->where('per_liano', $anio)
                    ->where('per_limes', $mes);
            })
            ->first();

        return [
            'total_aportes' => $result->total_aportes_sijp + $result->total_aportes_inssjp,
            'total_contribuciones' => $result->total_contribuciones_sijp + $result->total_contribuciones_inssjp,
            'total_remunerativo' => $result->total_remunerativo,
            'total_no_remunerativo' => $result->total_no_remunerativo,
        ];
    }
}
