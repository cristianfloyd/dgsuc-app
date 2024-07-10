<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AfipMapucheMiSimplificacion extends Model
{
    protected $connection = 'pgsql-mapuche';
    protected $table = 'suc.afip_mapuche_mi_simplificacion';
    protected $primaryKey = 'nro_legaj';
    public $timestamps = false;
    protected $collection;

    protected $fillable = [
        'nro_legaj',
        'nro_liqui',
        'sino_cerra',
        'desc_estado_liquidacion',
        'nro_cargo',
        'periodo_fiscal',
        'Tipo_de_registro',
        'codigo_movimiento',
        'CUIL',
        'Marca_de_trabajador_agropecuario',
        'Modalidad_de_contrato',
        'Fecha_inicio_de_rel_laboral',
        'Fecha_fin_rel_laboral',
        'Código_obra_social',
        'codigo_situacion_baja',
        'Fecha_telegrama_renuncia',
        'Retribución_pactada',
        'Modalidad_liquidación',
        'Sucursal',
        'Actividad',
        'Puesto',
        'Rectificacion',
        'C_C_C_Trabajo',
        'Tipo_servicio',
        'Categ_Prof',
        'Fecha_susp_serv_temp',
        'Número_Formulario_Agropecuario',
        'covid'
    ];

    public function scopeSearch($query, $value)
    {
        $query->where('cuil','like',"%{{$value}}%");
    }
}

