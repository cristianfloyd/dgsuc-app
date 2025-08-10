<?php

namespace App\Models\Suc;

use App\Traits\MapucheConnectionTrait;
use App\ValueObjects\Periodo;
use App\ValueObjects\TipoRetro;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RetResultado extends Model
{
    use HasFactory;
    use MapucheConnectionTrait;

    public $incrementing = false;

    public $timestamps = false;

    /**
     * Modelo para la tabla suc.ret_resultado que contiene datos de liquidaciones retroactivas mensuales.
     *
     * @property int $nro_legaj Número de legajo del empleado
     * @property int $nro_cargo_nuevo Número de cargo nuevo
     * @property int $nro_cargo_ant Número de cargo anterior
     * 
     * @property string $categ_n Categoría nueva
     * @property string|null $agrup_n Agrupamiento nuevo
     * @property string|null $dedid_n ID de dedicación nueva
     * @property float $cat_basico_n Importe básico categoría nueva
     * @property int $anios_n Años nueva posición
     * @property string|null $titulo_n Título nueva posición
     * @property int $anios_perm_n Años permanentes nueva posición
     * @property float $porcentaje_n Porcentaje nueva posición
     * @property int $hs_cat_n Horas categoría nueva posición
     * @property string|null $codc_uacad_n Código unidad académica nueva posición
     * @property string|null $coddependesemp_n Código dependencia empleado nueva posición
     * 
     * @property string $categ_v Categoría anterior
     * @property string|null $agrup_v Agrupamiento anterior
     * @property string|null $dedid_v ID de dedicación anterior
     * @property float $cat_basico_v Importe básico categoría anterior
     * @property int $anios_v Años posición anterior
     * @property string|null $titulo_v Título posición anterior
     * @property int $anios_perm_v Años permanentes posición anterior
     * @property float $porcentaje_v Porcentaje posición anterior
     * @property int $hs_cat_v Horas categoría posición anterior
     * @property string|null $codc_uacad_v Código unidad académica posición anterior
     * @property string|null $coddependesemp_v Código dependencia empleado posición anterior
     * 
     * @property bool $x11_n Bandera X11 nueva posición
     * @property bool $x11_v Bandera X11 posición anterior
     * @property bool $zona_n Bandera zona nueva posición
     * @property bool $zona_v Bandera zona posición anterior
     * @property bool $riesgo_n Bandera riesgo nueva posición
     * @property bool $riesgo_v Bandera riesgo posición anterior
     * @property bool $falla_n Bandera falla nueva posición
     * @property bool $falla_v Bandera falla posición anterior
     * @property bool $dede_n Bandera dedicación nueva posición
     * @property bool $dede_v Bandera dedicación posición anterior
     * @property bool $adi_col_sec_n Bandera adicional colegio secundario nueva posición
     * @property bool $adi_col_sec_v Bandera adicional colegio secundario posición anterior
     * 
     * @property string|null $sub_n Suplencia nueva posición
     * @property float $sub_basico_n Importe básico suplencia nueva posición
     * @property string|null $sub_v Suplencia posición anterior
     * @property float $sub_basico_v Importe básico suplencia posición anterior
     * 
     * @property float $porcehaber Porcentaje de haberes
     * @property int|null $dias_mes_trab Días del mes trabajados
     * @property \Carbon\Carbon $fecha_ret_desde Fecha inicio retroactivo
     * @property \Carbon\Carbon $fecha_ret_hasta Fecha fin retroactivo
     * @property string $periodo Período (formato YYYYMM)
     * @property string $liquida Bandera de liquidación
     * @property string|null $periodo_mens Período mensual
     * @property int $tipo_retro Tipo de retroactivo
     * @property int|null $porcentaje_dias_trab Porcentaje días trabajados
     * @property string|null $tipo_escal Tipo de escalafón
     * 
     * @property float $c101_n Concepto 101 nueva posición
     * @property float $c101_sub_n Concepto 101 suplencia nueva posición
     * @property float $c102_n Concepto 102 nueva posición
     * @property float $c103_n Concepto 103 nueva posición
     * @property float $c103_sub_n Concepto 103 suplencia nueva posición
     * @property int $c103_dias_cpto_trab Concepto 103 días trabajados
     * @property float $c106_n Concepto 106 nueva posición
     * @property float $c106_sub_n Concepto 106 suplencia nueva posición
     * @property int $c106_dias_cpto_trab Concepto 106 días trabajados
     * @property float $c107_n Concepto 107 nueva posición
     * @property float $c107_sub_n Concepto 107 suplencia nueva posición
     * @property float $c108_n Concepto 108 nueva posición
     * @property int $c108_dias_cpto_trab Concepto 108 días trabajados
     * @property float $c110_n Concepto 110 nueva posición
     * @property float $c110_sub_n Concepto 110 suplencia nueva posición
     * @property int $c110_dias_cpto_trab Concepto 110 días trabajados
     * @property float $c111_n Concepto 111 nueva posición
     * @property int $c111_dias_cpto_trab Concepto 111 días trabajados
     * @property float $c113_n Concepto 113 nueva posición
     * @property float $c113_sub_n Concepto 113 suplencia nueva posición
     * @property float $c114_n Concepto 114 nueva posición
     * @property float $c114_sub_n Concepto 114 suplencia nueva posición
     * @property float $c116_n Concepto 116 nueva posición
     * @property float $c116_sub_n Concepto 116 suplencia nueva posición
     * @property int $c116_dias_cpto_trab Concepto 116 días trabajados
     * @property float $c118_n Concepto 118 nueva posición
     * @property float $c118_sub_n Concepto 118 suplencia nueva posición
     * @property int $c118_dias_cpto_trab Concepto 118 días trabajados
     * @property float $c119_n Concepto 119 nueva posición
     * @property float $c119_sub_n Concepto 119 suplencia nueva posición
     * @property int $c119_dias_cpto_trab Concepto 119 días trabajados
     * @property float $c120_n Concepto 120 nueva posición
     * @property float $c138_n Concepto 138 nueva posición
     * @property float $c165_n Concepto 165 nueva posición
     * @property float $c173_n Concepto 173 nueva posición
     * @property float $c174_n Concepto 174 nueva posición
     * 
     * @property float $c101_v Concepto 101 posición anterior
     * @property float $c101_sub_v Concepto 101 suplencia posición anterior
     * @property float $c102_v Concepto 102 posición anterior
     * @property float $c103_v Concepto 103 posición anterior
     * @property float $c103_sub_v Concepto 103 suplencia posición anterior
     * @property float $c106_v Concepto 106 posición anterior
     * @property float $c106_sub_v Concepto 106 suplencia posición anterior
     * @property float $c107_v Concepto 107 posición anterior
     * @property float $c107_sub_v Concepto 107 suplencia posición anterior
     * @property float $c108_v Concepto 108 posición anterior
     * @property float $c110_v Concepto 110 posición anterior
     * @property float $c110_sub_v Concepto 110 suplencia posición anterior
     * @property float $c111_v Concepto 111 posición anterior
     * @property float $c113_v Concepto 113 posición anterior
     * @property float $c113_sub_v Concepto 113 suplencia posición anterior
     * @property float $c114_v Concepto 114 posición anterior
     * @property float $c114_sub_v Concepto 114 suplencia posición anterior
     * @property float $c116_v Concepto 116 posición anterior
     * @property float $c116_sub_v Concepto 116 suplencia posición anterior
     * @property float $c118_v Concepto 118 posición anterior
     * @property float $c118_sub_v Concepto 118 suplencia posición anterior
     * @property float $c119_v Concepto 119 posición anterior
     * @property float $c119_sub_v Concepto 119 suplencia posición anterior
     * @property float $c120_v Concepto 120 posición anterior
     * @property float $c138_v Concepto 138 posición anterior
     * @property float $c165_v Concepto 165 posición anterior
     * @property float $c173_v Concepto 173 posición anterior
     * @property float $c174_v Concepto 174 posición anterior
     * 
     * @property float $monto_180 Monto 180
     * @property float $monto_123 Monto 123
     * @property float $monto_168 Monto 168
     * @property float $cat_basico_7 Categoría básica 7
     * @property float $cat_basico_n_perm Categoría básica nueva permanente
     * @property float $cat_basico_v_perm Categoría básica anterior permanente
     * 
     * @property \App\ValueObjects\Periodo $periodo Período como ValueObject
     * @property \App\ValueObjects\TipoRetro $tipo_retro Tipo retroactivo como ValueObject
     *
     * La tabla asociada con el modelo.
     *
     * @var string
     */
    protected $table = 'suc.ret_resultado';

    // @phpstan-ignore property.defaultValue
    protected $primaryKey = ['nro_legaj', 'nro_cargo_ant', 'fecha_ret_desde', 'periodo'];

    
    protected $fillable = [
        'nro_legaj', 'nro_cargo_nuevo', 'nro_cargo_ant',
        'categ_n', 'agrup_n', 'dedid_n', 'cat_basico_n', 'anios_n', 'titulo_n',
        'anios_perm_n', 'porcentaje_n', 'hs_cat_n', 'codc_uacad_n', 'coddependesemp_n',
        'categ_v', 'agrup_v', 'dedid_v', 'cat_basico_v', 'anios_v', 'titulo_v',
        'anios_perm_v', 'porcentaje_v', 'hs_cat_v', 'codc_uacad_v', 'coddependesemp_v',
        'x11_n', 'x11_v', 'zona_n', 'zona_v', 'riesgo_n', 'riesgo_v',
        'falla_n', 'falla_v', 'dede_n', 'dede_v', 'adi_col_sec_n', 'adi_col_sec_v',
        'sub_n', 'sub_basico_n', 'sub_v', 'sub_basico_v',
        'porcehaber', 'dias_mes_trab', 'fecha_ret_desde', 'fecha_ret_hasta',
        'periodo', 'liquida', 'periodo_mens', 'tipo_retro', 'porcentaje_dias_trab', 'tipo_escal',
        'c101_n', 'c101_sub_n', 'c102_n', 'c103_n', 'c103_sub_n', 'c103_dias_cpto_trab',
        'c106_n', 'c106_sub_n', 'c106_dias_cpto_trab', 'c107_n', 'c107_sub_n',
        'c108_n', 'c108_dias_cpto_trab', 'c110_n', 'c110_sub_n', 'c110_dias_cpto_trab',
        'c111_n', 'c111_dias_cpto_trab', 'c113_n', 'c113_sub_n', 'c114_n', 'c114_sub_n',
        'c116_n', 'c116_sub_n', 'c116_dias_cpto_trab', 'c118_n', 'c118_sub_n', 'c118_dias_cpto_trab',
        'c119_n', 'c119_sub_n', 'c119_dias_cpto_trab', 'c120_n', 'c138_n', 'c165_n', 'c173_n', 'c174_n',
        'c101_v', 'c101_sub_v', 'c102_v', 'c103_v', 'c103_sub_v', 'c106_v', 'c106_sub_v',
        'c107_v', 'c107_sub_v', 'c108_v', 'c110_v', 'c110_sub_v', 'c111_v',
        'c113_v', 'c113_sub_v', 'c114_v', 'c114_sub_v', 'c116_v', 'c116_sub_v',
        'c118_v', 'c118_sub_v', 'c119_v', 'c119_sub_v', 'c120_v', 'c138_v', 'c165_v', 'c173_v', 'c174_v',
        'monto_180', 'monto_123', 'monto_168', 'cat_basico_7', 'cat_basico_n_perm', 'cat_basico_v_perm',
    ];

    protected $casts = [
        'cat_basico_n' => 'float',
        'cat_basico_v' => 'float',
        'anios_n' => 'integer',
        'anios_v' => 'integer',
        'anios_perm_n' => 'integer',
        'anios_perm_v' => 'integer',
        'porcentaje_n' => 'float',
        'porcentaje_v' => 'float',
        'hs_cat_n' => 'integer',
        'hs_cat_v' => 'integer',
        'x11_n' => 'boolean',
        'x11_v' => 'boolean',
        'zona_n' => 'boolean',
        'zona_v' => 'boolean',
        'riesgo_n' => 'boolean',
        'riesgo_v' => 'boolean',
        'falla_n' => 'boolean',
        'falla_v' => 'boolean',
        'dede_n' => 'boolean',
        'dede_v' => 'boolean',
        'adi_col_sec_n' => 'boolean',
        'adi_col_sec_v' => 'boolean',
        'sub_basico_n' => 'float',
        'sub_basico_v' => 'float',
        'porcehaber' => 'float',
        'dias_mes_trab' => 'integer',
        'porcentaje_dias_trab' => 'integer',
        'tipo_retro' => 'integer',
        'c101_n' => 'float',
        'c101_sub_n' => 'float',
        'c102_n' => 'float',
        'c103_n' => 'float',
        'c103_sub_n' => 'float',
        'c103_dias_cpto_trab' => 'integer',
        'c106_n' => 'float',
        'c106_sub_n' => 'float',
        'c106_dias_cpto_trab' => 'integer',
        'c107_n' => 'float',
        'c107_sub_n' => 'float',
        'c108_n' => 'float',
        'c108_dias_cpto_trab' => 'integer',
        'c110_n' => 'float',
        'c110_sub_n' => 'float',
        'c110_dias_cpto_trab' => 'integer',
        'c111_n' => 'float',
        'c111_dias_cpto_trab' => 'integer',
        'c113_n' => 'float',
        'c113_sub_n' => 'float',
        'c114_n' => 'float',
        'c114_sub_n' => 'float',
        'c116_n' => 'float',
        'c116_sub_n' => 'float',
        'c116_dias_cpto_trab' => 'integer',
        'c118_n' => 'float',
        'c118_sub_n' => 'float',
        'c118_dias_cpto_trab' => 'integer',
        'c119_n' => 'float',
        'c119_sub_n' => 'float',
        'c119_dias_cpto_trab' => 'integer',
        'c120_n' => 'float',
        'c138_n' => 'float',
        'c165_n' => 'float',
        'c173_n' => 'float',
        'c174_n' => 'float',
        'c101_v' => 'float',
        'c101_sub_v' => 'float',
        'c102_v' => 'float',
        'c103_v' => 'float',
        'c103_sub_v' => 'float',
        'c106_v' => 'float',
        'c106_sub_v' => 'float',
        'c107_v' => 'float',
        'c107_sub_v' => 'float',
        'c108_v' => 'float',
        'c110_v' => 'float',
        'c110_sub_v' => 'float',
        'c111_v' => 'float',
        'c113_v' => 'float',
        'c113_sub_v' => 'float',
        'c114_v' => 'float',
        'c114_sub_v' => 'float',
        'c116_v' => 'float',
        'c116_sub_v' => 'float',
        'c118_v' => 'float',
        'c118_sub_v' => 'float',
        'c119_v' => 'float',
        'c119_sub_v' => 'float',
        'c120_v' => 'float',
        'c138_v' => 'float',
        'c165_v' => 'float',
        'c173_v' => 'float',
        'c174_v' => 'float',
        'monto_180' => 'float',
        'monto_123' => 'float',
        'monto_168' => 'float',
        'cat_basico_7' => 'float',
        'cat_basico_n_perm' => 'float',
        'cat_basico_v_perm' => 'float',
        'fecha_ret_desde' => 'date',
        'fecha_ret_hasta' => 'date',
    ];

    /**
     * Obtiene el resultado del retroactivo para un legajo específico.
     *
     * @param int $nroLegaj
     *
     * @return RetResultado|null
     */
    public static function obtenerPorLegajo(int $nroLegaj): ?RetResultado
    {
        return self::where('nro_legaj', $nroLegaj)->first();
    }

    /**
     * Obtiene el periodo como un ValueObject Periodo.
     *
     * @return Periodo
     */
    public function getPeriodoAttribute(): Periodo
    {
        return new Periodo($this->attributes['periodo']);
    }

    /**
     * Establece el periodo desde un ValueObject Periodo.
     *
     * @param Periodo $periodo
     */
    public function setPeriodoAttribute(Periodo $periodo): void
    {
        $this->attributes['periodo'] = $periodo->getValue();
    }

    /**
     * Obtiene el tipo_retro como un ValueObject TipoRetro.
     *
     * @return TipoRetro
     */
    public function getTipoRetroAttribute(): TipoRetro
    {
        return new TipoRetro($this->attributes['tipo_retro']);
    }

    /**
     * Establece el tipo_retro desde un ValueObject TipoRetro.
     *
     * @param TipoRetro $tipoRetro
     */
    public function setTipoRetroAttribute(TipoRetro $tipoRetro): void
    {
        $this->attributes['tipo_retro'] = $tipoRetro->getValue();
    }
}
