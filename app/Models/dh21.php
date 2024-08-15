<?php

namespace App\Models;

use App\Models\Mapuche\Dh22;
use App\Traits\MapucheConnectionTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class dh21 extends Model
{
    use MapucheConnectionTrait;
    protected $table = 'mapuche.dh21';
    public $timestamps = false;
    protected $primaryKey = 'id_liquidacion';

    protected $fillable = [
        'nro_liqui',
        'nro_legaj',
        'nro_cargo',
        'codn_conce',
        'impp_conce',
        'tipo_conce',
        'nov1_conce',
        'nov2_conce',
        'nro_orimp',
        'tipoescalafon',
        'nrogrupoesc',
        'codigoescalafon',
        'codc_regio',
        'codc_uacad',
        'codn_area',
        'codn_subar',
        'codn_fuent',
        'codn_progr',
        'codn_subpr',
        'codn_proye',
        'codn_activ',
        'codn_obra',
        'codn_final',
        'codn_funci',
        'ano_retro',
        'mes_retro',
        'detallenovedad',
        'codn_grupo_presup',
        'tipo_ejercicio',
        'codn_subsubar'
    ];


    /**
     * Relación de pertenencia con el modelo Dh22.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function dh22()
    {
        return $this->belongsTo(Dh22::class, 'nro_liqui', 'nro_liqui');
    }
    public function scopeSearch($query, $search)
    {
        return $query->where('nro_legaj', 'like', '%' . $search . '%')
            ->orWhere('nro_liqui', 'like', "%$search%")
            ->orWhere('nro_cargo', 'like', "%$search%")
            ->orWhere('codn_conce', 'like', "%$search%")
            ->orWhere('impp_conce', 'like', "%$search%")
            ->orWhere('tipo_conce', 'like', "%$search%")
            ->orWhere('nov1_conce', 'like', "%$search%")
            ->orWhere('nov2_conce', 'like', "%$search%")
            ->orWhere('nro_orimp', 'like', "%$search%")
            ->orWhere('tipoescalafon', 'like', '%' . $search . '%')
            ->orWhere('nrogrupoesc', 'like', '%' . $search . '%')
            ->orWhere('codigoescalafon', 'like', "%$search%")
            ->orWhere('codc_regio', 'like', '%' . $search . '%')
            ->orWhere('codc_uacad', 'like', '%' . $search . '%')
            ->orWhere('codn_area', 'like', '%' . $search . '%')
            ->orWhere('codn_subar', 'like', '%' . $search . '%')
            ->orWhere('codn_fuent', 'like', '%' . $search . '%')
            ->orWhere('codn_progr', 'like', '%' . $search . '%')
            ->orWhere('codn_subpr', 'like', '%' . $search . '%')
            ->orWhere('codn_proye', 'like', '%' . $search . '%')
            ->orWhere('codn_activ', 'like', '%' . $search . '%')
            ->orWhere('codn_obra', 'like', '%' . $search . '%')
            ->orWhere('codn_final', 'like', '%' . $search . '%')
            ->orWhere('codn_funci', 'like', '%' . $search . '%')
            ->orWhere('ano_retro', 'like', '%' . $search . '%')
            ->orWhere('mes_retro', 'like', '%' . $search . '%')
            ->orWhere('detallenovedad', 'like', '%' . $search . '%');
    }

    /**
     * Obtiene la cantidad de legajos distintos en la tabla.
     *
     * @return int
     */
    public static function distinctLegajos()
    {
        return static::query()->distinct('nro_legaj')->count();
    }

    public static function distinctCargos()
    {
        return static::query()->distinct('nro_cargo')->count();
    }

    /**
     * Obtiene la suma total del concepto 101 en la tabla.
     *
     * @return float
     */
    public static function totalConcepto101()
    {
        return static::query()->where('codn_conce', '101')->sum('impp_conce');
    }

    /**
     * Obtiene la suma total de todos los conceptos en la tabla.
     *
     * @return Collection
     */
    public static function totalConceptos()
    {
        return static::query()
            ->select('codn_conce', 'impp_conce')
            ->get()
            ->groupBy('codn_conce')
            ->orderBy('codn_conce')
            ->map(function ($group) {
                return $group->sum('impp_conce');
            });
    }

    /**
     * Obtiene los totales de cada concepto en la tabla.
     *
     * @return Builder
     */
    public static function conceptosTotales()
    {
        return static::query()
            ->select(
                DB::raw('ROW_NUMBER() OVER (ORDER BY codn_conce) as id_liquidacion'),
                'codn_conce',
                DB::raw('SUM(impp_conce) as total_impp'))
            ->where('nro_liqui', '=', 2)
            ->where('codn_conce', '>', '100')
            ->whereRaw('codn_conce/100 IN (1,3)')
            ->groupBy('codn_conce')
            ->orderBy('codn_conce');
    }

    private function getLiquidaciones()
    {
        return $this->belongsTo(Dh22::class, 'nro_liqui', 'nro_liqui');
    }
}
