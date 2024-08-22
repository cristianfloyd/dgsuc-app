<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AfipMapucheParaSicoss extends Model
{
    protected $connection = 'pgsql-mapuche';
    protected $table = 'suc.afip_mapuche_para_sicoss';
    protected $primaryKey = 'nro_legaj';
    public $timestamps = false;
    protected $collection;

    protected $fillable = [
        'nro_liqui',
        'sino_cerra',
        'desc_estado_liquidacion',
        'nro_cargo',
        'tipo_registro',
        'codigo_movimiento',
        'cuil',
        'trabajador_agropecuario',
        'modalidad_contrato',
        'inicio_rel_laboral',
        'fin_rel_laboral',
        'obra_social',
        'codigo_situacion_baja',
        'fecha_tel_renuncia',
        'retribucion_pactada',
        'modalidad_liquidaicon',
        'domicilio',
        'actividad',
        'puesto',
        'rectificacion',
        'ccct',
        'tipo_servicio',
        'categoria',
        'fecha_suspencion_servicios',
        'no_form_agro',
        'covid'
    ];

    public function getNroLegajAttribute($value)
    {
        return str_pad($value, 8, '0', STR_PAD_LEFT);
    }

    /**
     * Consulta SQL que devuelve un conjunto de registros para armar la tabla `suc.afip_mapuche_sicoss` con los siguientes campos:
     * - `nro_legaj`: Número de legajo
     * - `nro_liqui`: Número de liquidación
     * - `sino_cerra`: Indicador de si la liquidación está cerrada
     * - `desc_estado_liquidacion`: Descripción del estado de la liquidación
     * - `nro_cargo`: Número de cargo
     * - `tipo_registro`: Tipo de registro, siempre '01'
     * - `codigo_movimiento`: Código de movimiento, siempre 'AT'
     * - `cuil`: Número de CUIL concatenado
     * - `trabajador_agropecuario`: Indicador de si es un trabajador agropecuario, siempre 'N'
     * - `modalidad_contrato`: Modalidad de contrato, siempre '008'
     * - `inicio_rel_laboral`: Fecha de inicio de la relación laboral
     * - `fin_rel_laboral`: Fecha de fin de la relación laboral
     * - `obra_social`: Código de obra social, siempre '000000'
     * - `codigo_situacion_baja`: Código de situación de baja, siempre '01'
     * - `fecha_tel_renuncia`: Fecha de renuncia telefónica, siempre '0000000000'
     * - `retribucion_pactada`: Retribución pactada
     * - `modalidad_liquidaicon`: Modalidad de liquidación, siempre 1
     * - `domicilio`: Código de domicilio
     * - `actividad`: Actividad, siempre NULL
     * - `puesto`: Puesto, siempre NULL
     * - `rectificacion`: Rectificación, siempre '00'
     * - `ccct`: Convenio colectivo de trabajo, siempre '9999/99'
     * - `tipo_servicio`: Tipo de servicio, siempre '000'
     * - `categoria`: Categoría, siempre '000000'
     * - `fecha_suspencion_servicios`: Fecha de suspensión de servicios, siempre NULL
     * - `no_form_agro`: Número de formulario agropecuario, siempre '0000000000'
     * - `covid`: Indicador de COVID, siempre '0'
     *
     * La consulta filtra los registros donde `d.tipo_conce` es 'C' y `d3.chkstopliq` es 0, y los agrupa por los campos mencionados anteriormente.
     */
    public static function scopeSicossAll($query)
        {
        // Consulta SQL
        return $query->selectRaw("
                d4.nro_liqui,
                d4.sino_cerra,
                el.desc_estado_liquidacion,
                d.nro_cargo,
                '01' AS tipo_registro,
                'AT' AS codigo_movimiento,
                concat(d2.nro_cuil1 , d2.nro_cuil , d2.nro_cuil2) AS cuil,
                'N' AS trabajador_agropecuario,
                '008' AS modalidad_contrato,
                d3.fec_alta AS inicio_rel_laboral,
                d3.fec_baja AS fin_rel_laboral,
                '000000' AS obra_social,
                '01' AS codigo_situacion_baja,
                '0000000000' AS fecha_tel_renuncia,
                sum(d.impp_conce) AS retribucion_pactada,
                1 AS modalidad_liquidaicon,
                d3.codc_uacad AS domicilio,
                NULL AS actividad,
                NULL AS puesto,
                '00' AS rectificacion,
                '9999/99' AS ccct,
                '000' AS tipo_servicio,
                '000000' AS categoria,
                NULL AS fecha_suspencion_servicios,
                '0000000000' AS nro_form_agro,
                '0' AS covid
            ")
            ->from('mapuche.dh21 as d')
            ->join('mapuche.dh01 as d2', 'd.nro_legaj', '=', 'd2.nro_legaj')
            ->join('mapuche.dh03 as d3', 'd.nro_cargo', '=', 'd3.nro_cargo')
            ->join('mapuche.dh22 as d4', 'd.nro_liqui', '=', 'd4.nro_liqui')
            ->join('mapuche.estado_liquidacion as el', 'd4.sino_cerra', '=', 'el.cod_estado_liquidacion')
            ->where('d.tipo_conce', 'C')
            ->where('d3.chkstopliq', 0)
            ->groupBy('d.nro_legaj', 'd4.nro_liqui', 'el.desc_estado_liquidacion', 'd.nro_cargo','d2.nro_cuil1', 'd2.nro_cuil', 'd2.nro_cuil2', 'd3.fec_alta', 'd3.fec_baja', 'd3.codc_uacad')
            ->orderBy('d.nro_legaj', 'asc')
            ->orderBy('d.nro_cargo', 'asc');
    }
}
