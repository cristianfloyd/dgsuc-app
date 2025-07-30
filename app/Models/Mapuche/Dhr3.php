<?php

declare(strict_types=1);

namespace App\Models\Mapuche;

use App\Models\Dh12;
use App\Traits\HasCompositePrimaryKey;
use App\Traits\MapucheConnectionTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Modelo Dhr3 para gestión de conceptos de liquidación Mapuche
 *
 * @property int $nro_liqui Número de liquidación
 * @property int $nro_legaj Número de legajo
 * @property int $nro_cargo Número de cargo
 * @property string $codc_hhdd Código HHDD
 * @property int $nro_renglo Número de renglón
 * @property int|null $nro_conce Número de concepto
 * @property string|null $desc_conc Descripción del concepto
 * @property float|null $novedad1 Primera novedad
 * @property float|null $novedad2 Segunda novedad
 * @property float|null $impo_conc Importe del concepto
 * @property int|null $ano_retro Año retroactivo
 * @property int|null $mes_retro Mes retroactivo
 * @property int|null $nro_recibo Número de recibo
 * @property string|null $observa Observaciones
 * @property string|null $tipo_conce Tipo de concepto
 */
class Dhr3 extends Model
{
    use HasFactory;
    use MapucheConnectionTrait;

    protected $table = 'dhr3';
    public $timestamps = false;
    protected $primaryKey = ['nro_liqui', 'nro_legaj', 'nro_cargo', 'codc_hhdd', 'nro_renglo'];
    public $incrementing = false;

    protected $fillable = [
        'nro_liqui', 'nro_legaj', 'nro_cargo', 'codc_hhdd', 'nro_renglo',
        'nro_conce', 'desc_conc', 'novedad1', 'novedad2', 'impo_conc',
        'ano_retro', 'mes_retro', 'nro_recibo', 'observa', 'tipo_conce'
    ];

    protected $casts = [
        'nro_liqui' => 'integer',
        'nro_legaj' => 'integer',
        'nro_cargo' => 'integer',
        'codc_hhdd' => 'string',
        'nro_renglo' => 'integer',
        'nro_conce' => 'integer',
        'desc_conc' => 'string',
        'novedad1' => 'float',
        'novedad2' => 'float',
        'impo_conc' => 'float',
        'ano_retro' => 'integer',
        'mes_retro' => 'integer',
        'nro_recibo' => 'integer',
        'observa' => 'string',
        'tipo_conce' => 'string',
    ];

    /**
     * Relación con la tabla dh12 (conceptos)
     */
    public function concepto(): BelongsTo
    {
        return $this->belongsTo(Dh12::class, 'nro_conce', 'codn_conce');
    }

    /**
     * Relación con la tabla dhr2 (liquidación-legajo)
     */
    public function liquidacionLegajo(): BelongsTo
    {
        return $this->belongsTo(Dhr2::class, 'nro_liqui', 'nro_liqui')
            ->where('nro_leagj', $this->nro_legaj)
            ->where('nro_cargo', $this->nro_cargo);
    }
}
