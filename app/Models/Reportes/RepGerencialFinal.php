<?php

namespace App\Models\Reportes;

use Spatie\LaravelData\Data;
use App\Models\Mapuche\Catalogo\Dh36;
use App\Models\Mapuche\Catalogo\Dhe4;
use App\Traits\MapucheConnectionTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Modelo Eloquent para la tabla rep_ger_final
 *
 * @property int $codn_fuent Código de fuente
 * @property int $codn_depen Código de dependencia
 * @property string $tipo_ejercicio Tipo de ejercicio
 * @property string $codn_grupo_presup Código grupo presupuestario
 * @property string $codn_area Código de área
 * @property string $codn_subar Código de subárea
 * @property string $codn_subsubar Código de sub-subárea
 * @property float $imp_gasto Importe gasto
 * @property float $imp_bruto Importe bruto
 * @property float $imp_neto Importe neto
 * @property float $imp_dctos Importe descuentos
 * @property float $imp_aport Importe aportes
 * @property float $imp_familiar Importe asignaciones familiares
 */
class RepGerencialFinal extends Model
{
    use HasFactory, MapucheConnectionTrait;

    /**
     * Nombre de la tabla en la base de datos
     */
    protected $table = 'suc.rep_ger_final';
    protected $primaryKey = 'id';
    public $incrementing = true;

    /**
     * Indica si el modelo debe tener timestamps
     */
    public $timestamps = false;

    /**
     * Los atributos que son asignables masivamente
     */
    protected $fillable = [
        'codn_fuent', 'codn_depen', 'tipo_ejercicio', 'codn_grupo_presup',
        'codn_area', 'codn_subar', 'codn_subsubar', 'codn_progr', 'codn_subpr',
        'codn_proye', 'codn_activ', 'codn_obra', 'codn_final', 'codn_funci',
        'codn_imput', 'imputacion', 'nro_inciso', 'nro_legaj', 'desc_apyno',
        'nombre_elegido', 'apellido_elegido', 'cant_anios', 'ano_antig',
        'mes_antig', 'nro_cargo', 'codc_categ', 'codc_dedic', 'tipo_escal',
        'codc_carac', 'codc_uacad', 'codc_regio', 'fecha_alta', 'fecha_baja',
        'porc_imput', 'imp_gasto', 'imp_bruto', 'imp_neto', 'imp_dctos',
        'imp_aport', 'imp_familiar', 'ano_liqui', 'mes_liqui', 'nro_liqui',
        'tipo_estad', 'cuil', 'hs_catedra', 'dias_trab', 'rem_c_apor',
        'otr_no_rem', 'en_banco', 'coddependesemp', 'porc_aplic',
        'cod_clasif_cargo', 'tipo_carac', 'rem_s_apor'
    ];

    /**
     * Los atributos que deben ser convertidos a tipos nativos
     */
    protected $casts = [
        'codn_fuent' => 'integer',
        'codn_depen' => 'integer',
        'nro_inciso' => 'integer',
        'nro_legaj' => 'integer',
        'cant_anios' => 'integer',
        'ano_antig' => 'float',
        'mes_antig' => 'float',
        'nro_cargo' => 'integer',
        'fecha_alta' => 'date',
        'fecha_baja' => 'date',
        'porc_imput' => 'decimal:2',
        'imp_gasto' => 'decimal:2',
        'imp_bruto' => 'decimal:2',
        'imp_neto' => 'decimal:2',
        'imp_dctos' => 'decimal:2',
        'imp_aport' => 'decimal:2',
        'imp_familiar' => 'decimal:2',
        'ano_liqui' => 'integer',
        'mes_liqui' => 'integer',
        'nro_liqui' => 'integer',
        'hs_catedra' => 'float',
        'dias_trab' => 'float',
        'rem_c_apor' => 'decimal:2',
        'otr_no_rem' => 'decimal:2',
        'porc_aplic' => 'float',
        'cod_clasif_cargo' => 'integer',
        'rem_s_apor' => 'decimal:2'
    ];


    /* #################### SCOPES #################### */

    /**
     * Scope para filtrar por liquidación
     */
    public function scopeLiquidacion(Builder $query, int $nroLiqui): Builder
    {
        return $query->where('nro_liqui', $nroLiqui);
    }

    /**
     * Scope para filtrar por dependencia
     */
    public function scopeDependencia(Builder $query, string $codDependencia): Builder
    {
        return $query->where('coddependesemp', $codDependencia);
    }

    /* #################### ACCESSORS #################### */
    /**
     * Accessor para obtener nombre completo
     */
    public function getNombreCompletoAttribute(): string
    {
        return "{$this->apellido_elegido}, {$this->nombre_elegido}";
    }

    /* #################### RELACIONES #################### */
    public function dependencia(): BelongsTo
    {
        return $this->belongsTo(Dh36::class, 'coddependesemp', 'coddependesemp');
    }

    public function unidadAcademica(): BelongsTo
    {
        return $this->belongsTo(Dhe4::class, 'codc_uacad', 'cod_organismo');
    }
}
