<?php

namespace App\Models\Mapuche;

use Spatie\LaravelData\WithData;
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
 * ...
 */
class Dh21h extends Model
{
    use MapucheConnectionTrait, Dh21hQueries, WithData;



    /**
     * Nombre de la tabla
     */
    protected $table = 'dh21h';

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

    /**
     * Query Scopes
     */
    public function scopeByLegajo($query, int $legajo)
    {
        return $query->where('nro_legaj', $legajo);
    }

    public function scopeByPeriodo($query, int $año, int $mes)
    {
        return $query->where('ano_retro', $año)
                    ->where('mes_retro', $mes);
    }

    /**
     * Accesors & Mutators
     */
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

