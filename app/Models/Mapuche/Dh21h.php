<?php

namespace App\Models\Mapuche;

use Spatie\LaravelData\WithData;
use Illuminate\Support\Facades\DB;
use App\Traits\Mapuche\Dh21hQueries;
use App\Traits\MapucheConnectionTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Modelo para la tabla de liquidaciones del sistema Mapuche
 *
 * @property int $id_liquidacion
 * @property int|null $nro_liqui
 * @property int|null $nro_legaj
 * @property int|null $nro_cargo
 * @property int|null $codn_conce
 * @property float|null $impp_conce
 * @property string|null $tipo_conce
 * @property float|null $nov1_conce
 * @property float|null $nov2_conce
 *
 */
class Dh21h extends Model
{
    use MapucheConnectionTrait;
    use Dh21hQueries;
    use WithData;
    use HasFactory;



    /**
     * Nombre de la tabla
     */
    protected $table = 'dh21h';
    protected $schema = 'mapuche';

    /**
     * Llave primaria
     */
    protected $primaryKey = 'id_liquidacion';

    /**
     * Desactivar timestamps de Laravel
     */
    public $timestamps = false;

    /**
     * Atributos asignables masivamente
     */
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
     * Casteos de atributos
     */
    protected $casts = [
        'impp_conce' => 'float',
        'nov1_conce' => 'float',
        'nov2_conce' => 'float',
        'tipo_conce' => 'string',
        'tipoescalafon' => 'string',
        'codigoescalafon' => 'string',
        'tipo_ejercicio' => 'string'
    ];



    /**####################### SCOPES ################################### **/

    public function scopeByLegajo($query, int $legajo)
    {
        return $query->where('nro_legaj', $legajo);
    }

    public function scopeByPeriodo($query, int $año, int $mes)
    {
        return $query->where('ano_retro', $año)
                    ->where('mes_retro', $mes);
    }

    public function scopeLegajosActivos($query)
    {
        return $query->where('nro_cargo', '>', 0);
    }

    public function scopeConDatosPersonales($query)
    {
        return $query->join('dh01', 'dh21h.nro_legaj', '=', 'dh01.nro_legaj')
            ->select(
                'dh21h.*',
                'dh01.desc_appat',
                'dh01.desc_nombr',
                DB::raw("CONCAT(dh01.nro_cuil1, dh01.nro_cuil, dh01.nro_cuil2) as cuil")
            );
    }

    public function scopeDefinitiva($query)
    {
        return $query->whereRaw("LOWER(desc_liqui) LIKE '%definitiva%'");
    }

    public function scopeEntreFechas($query, $fechaInicio, $fechaFin)
    {
        return $query->join('dh22', 'dh21h.nro_liqui', '=', 'dh22.nro_liqui')
        ->where(function ($q) use ($fechaInicio, $fechaFin) {
            $q->where(function ($inner) use ($fechaInicio, $fechaFin) {
                $inner->where('dh22.per_liano', $fechaInicio->year)
                    ->whereBetween('dh22.per_limes', [$fechaInicio->month, $fechaFin->month]);
            })->orWhere(function ($inner) use ($fechaInicio, $fechaFin) {
                $inner->whereBetween('dh22.per_liano', [$fechaInicio->year, $fechaFin->year])
                    ->where('dh22.per_limes', '>=', $fechaInicio->month)
                    ->where('dh22.per_limes', '<=', $fechaFin->month);
            });
        })
        ->whereRaw("LOWER(dh22.desc_liqui) LIKE '%definitiva%'");
    }

    /** ############################### Accesors & Mutators ############################### */

    protected function importeConcepto(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->impp_conce,
            set: fn ($value) => round($value, 2)
        );
    }

    // ############################### Relaciones #######################################
    public function dh22(): BelongsTo
    {
        return $this->belongsTo(Dh22::class, 'nro_liqui', 'nro_liqui');
    }
}
