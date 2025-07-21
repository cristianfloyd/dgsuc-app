<?php

namespace App\Models\Mapuche;

use App\Traits\MapucheConnectionTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Modelo para la tabla de liquidaciones de haberes
 *
 * @property int $nro_liqui Número de liquidación
 * @property int $nro_legaj Número de legajo
 * @property int $nro_cargo Número de cargo
 * @property string|null $desc_apyno Apellido y nombre
 * ...
 */
class Dhr2 extends Model
{
    use HasFactory;
    use MapucheConnectionTrait;


    protected $table = 'mapuche.dhr2';
    public $timestamps = false;
    protected $primaryKey = ['nro_liqui', 'nro_legaj', 'nro_cargo'];
    public $incrementing = false;

    /**
     * Atributos asignables masivamente
     */
    protected $fillable = [
        'nro_liqui', 'nro_legaj', 'nro_cargo', 'desc_apyno',
        'tipo_docum', 'nro_docum', 'nro_cuil1', 'nro_cuil',
        'nro_cuil2', 'codc_uacad', 'codc_categ', 'codc_dedic',
        'tot_haber', 'tot_reten', 'tot_neto', 'nro_recibo',
        'dias_trab', 'hs_dedica', 'obrasocial', 'dias_retro',
        'tipocuenta', 'ctabanco', 'codbanco', 'texto1',
        'texto2', 'texto3', 'texto4', 'codc_regio',
        'anulado', 'impreso'
    ];

    /**
     * Casteos de atributos
     */
    protected $casts = [
        'nro_liqui' => 'integer',
        'nro_legaj' => 'integer',
        'nro_cargo' => 'integer',
        'desc_apyno' => 'string',
        'nro_docum' => 'integer',
        'nro_cuil1' => 'integer',
        'nro_cuil' => 'integer',
        'nro_cuil2' => 'integer',
        'tot_haber' => 'float',
        'tot_reten' => 'float',
        'tot_neto' => 'float',
        'hs_dedica' => 'float',
        'anulado' => 'boolean',
        'impreso' => 'boolean'
    ];

    /**
     * Relación con la liquidación principal
     */
    public function liquidacion(): BelongsTo
    {
        return $this->belongsTo(Dhr1::class, 'nro_liqui', 'nro_liqui');
    }

    /**
     * Scope para filtrar por legajo
     */
    public function scopePorLegajo($query, $legajo): mixed
    {
        return $query->where('nro_legaj', $legajo);
    }

    /**
     * Mutados para desc_apyno en mayusculas
     */
    public function getDescApynoAttribute($value)
    {
        return mb_strtoupper($value);
    }
}
