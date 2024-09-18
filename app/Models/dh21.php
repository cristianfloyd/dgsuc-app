<?php

namespace App\Models;

use App\Models\Mapuche\Dh22;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Traits\MapucheConnectionTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Dh21 extends Model
{
    use MapucheConnectionTrait;
    protected $table = 'dh21';
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
    public function dh22(): BelongsTo
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

    public static function distinctCodigoEscalafon(): array
    {
        return static::query()->distinct('codigoescalafon')->get()->pluck('codigoescalafon')->toArray();
    }

    /**
     * Obtiene la suma total del concepto 101 en la tabla.
     *
     * @return float
     */
    public static function totalConcepto101(int $nro_liqui = null)
    {
        $query = static::query()->where('codn_conce', '101');

        if ($nro_liqui !== null) {
            $query->where('nro_liqui', $nro_liqui);
        }

        return $query->sum('impp_conce');
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
     * Obtiene la relación de liquidaciones asociadas a este registro.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    private function getLiquidaciones()
    {
        return $this->belongsTo(Dh22::class, 'nro_liqui', 'nro_liqui');
    }

    /**
     * Obtiene los totales de cada concepto en la tabla.
     *
     * @param int|null $nro_liqui Número de liquidación (opcional)
     * @param int|null $codn_fuent Código de fuente (opcional)
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function conceptosTotales(int $nro_liqui = null, int $codn_fuent = null): Builder
    {
        try {
            // Construcción de la consulta base
            $query = static::query()
                ->select(
                    DB::raw('ROW_NUMBER() OVER (ORDER BY codn_conce) as id_liquidacion'),
                    'codn_conce',
                    DB::raw('SUM(impp_conce) as total_impp')
                )
                // Filtro opcional por nro_liqui
                ->when($nro_liqui !== null, function ($query) use ($nro_liqui) {
                    return $query->where('nro_liqui', '=', $nro_liqui);
                })
                // Filtro por codn_conce mayor a 100
                ->where('codn_conce', '>', '100')
                // Filtro opcional por codn_fuent
                ->when($codn_fuent !== null, function ($query) use ($codn_fuent) {
                    return $query->where('codn_fuent', '=', $codn_fuent);
                })
                // Filtro adicional por codn_conce
                ->whereRaw('codn_conce/100 IN (1,3)')
                // Agrupación por codn_conce
                ->groupBy('codn_conce')
                // Ordenación por codn_conce
                ->orderBy('codn_conce');

            return $query;
        } catch (\Exception $e) {
            // Manejo de excepciones
            Log::error('Error en conceptosTotales: ' . $e->getMessage());
            throw $e;
        }
    }
}
