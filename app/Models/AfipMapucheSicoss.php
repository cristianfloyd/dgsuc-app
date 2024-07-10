<?php

namespace App\Models;

use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Log;

class AfipMapucheSicoss extends Model
{
    use HasFactory;

    // Especificar la tabla
    protected $connection = 'pgsql-mapuche';
    protected $table = 'suc.afip_mapuche_sicoss';

    // Especificar la clave primaria compuesta de la tabla periodo_fiscal y cuil
    protected $primaryKey = ['periodo_fiscal', 'cuil'];
    public $incrementing = false;
    // No necesitas usar timestamps
    public $timestamps = false;

    // Agregar las columnas que pueden ser asignadas masivamente
    protected $fillable = [
        'periodo_fiscal',
        'cuil',
        'apnom',
        'conyuge',
        'cant_hijos',
        'cod_situacion',
        'cod_cond',
        'cod_act',
        'cod_zona',
        'porc_aporte',
        'cod_mod_cont',
        'cod_os',
        'cant_adh',
        'rem_total',
        'rem_impo1',
        'asig_fam_pag',
        'aporte_vol',
        'imp_Adic_os',
        'exc_aport_ss',
        'exc_aport_os',
        'prov',
        'rem_Impo2',
        'rem_Impo3',
        'rem_Impo4',
        'cod_siniestrado',
        'marca_reduccion',
        'recomp_lrt',
        'tipo_empresa',
        'aporte_adic_os',
        'regimen',
        'sit_rev1',
        'dia_ini_sit_rev1',
        'sit_rev2',
        'dia_ini_sit_rev2',
        'sit_rev3',
        'dia_ini_sit_rev3',
        'sueldo_adicc',
        'sac',
        'horas_extras',
        'zona_desfav',
        'vacaciones',
        'cant_dias_trab',
        'rem_impo5',
        'convencionado',
        'rem_impo6',
        'tipo_oper',
        'adicionales',
        'premios',
        'rem_dec_788_05',
        'rem_imp7',
        'nro_horas_ext',
        'cpto_no_remun',
        'maternidad',
        'rectificacion_remun',
        'rem_Imp9',
        'contrib_dif',
        'hstrab',
        'seguro',
        'ley_27430',
        'incsalarial',
        'remimp11',
    ];

    public function dh01()
    {
        return $this->belongsTo(Dh01::class, 'cuil', 'cuil_completo');
    }


    public function scopeSearch($query, $search)
    {
        return $query->where('cuil', 'ilike', '%' . $search . '%')
            ->orWhere('apnom', 'ilike', '%' . $search . '%');
    }

    // Agregar un nuevo método para obtener el periodo fiscal formateado si es necesario
    public function getPeriodoFiscalFormateado()
    {
        $periodo = $this->attributes['periodo_fiscal'];
        return substr($periodo, 0, 4) . '-' . substr($periodo, 4, 2);
    }


    public function getKey()
    {
        return $this->periodo_fiscal . '|' . $this->cuil;
    }

    public function getKeyName()
    {
        return ['periodo_fiscal', 'cuil'];
    }
    public function getRouteKey()
    {
        return "{$this->periodo_fiscal}|{$this->cuil}";
    }
    public function getRouteKeyName() {
        return 'unique_id';
    }


    public function getUniqueIdAttribute()
    {
        return "{$this->periodo_fiscal}|{$this->cuil}";
    }

    public function resolveRouteBinding($value, $field = null)
    {
        [$periodo_fiscal, $cuil] = explode('|', $value);
        return $this->where('periodo_fiscal', $periodo_fiscal)
                    ->where('cuil', $cuil)
                    ->firstOrFail();
    }

}

