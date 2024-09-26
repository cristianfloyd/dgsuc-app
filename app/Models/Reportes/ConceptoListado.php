<?php

namespace App\Models\Reportes;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class ConceptoListado extends Model
{
    protected $table = 'concepto_listado';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'codc_uacad',
        'periodo_fiscal',
        'nro_liqui',
        'desc_liqui',
        'nro_legaj',
        'cuil',
        'apellido',
        'nombre',
        'oficina_pago',
        'codc_categ',
        'codigoescalafon',
        'secuencia',
        'categoria_completa',
        'codn_conce',
        'tipo_conce',
        'impp_conce'
    ];

    public function newQuery()
    {
        return parent::newQuery()
            ->from(DB::raw('(' . $this->getSqlQuery() . ') as concepto_listado'));
    }

    private function getSqlQuery()
    {
        return "
            SELECT
                d3.codc_uacad,
                CONCAT(d22.per_liano, d22.per_limes) AS periodo_fiscal,
                d22.desc_liqui,
                d.nro_legaj,
                CONCAT(d.nro_cuil1, d.nro_cuil, d.nro_cuil2) AS cuil,
                d.desc_appat AS apellido,
                d.desc_nombr AS nombre,
                d3.coddependesemp AS oficina_pago,
                d11.codc_categ,
                d11.codigoescalafon,
                'secuencia' AS secuencia,
                CONCAT(d11.desc_categ, d3.codc_agrup, ' ', d3.codc_carac) AS categoria_completa,
                d21.codn_conce,
                d21.tipo_conce,
                d21.impp_conce
            FROM mapuche.dh01 d
            JOIN mapuche.dh03 d3 ON d.nro_legaj = d3.nro_legaj
            JOIN mapuche.dh11 d11 ON d3.codc_categ = d11.codc_categ
            JOIN mapuche.dh21 d21 ON d.nro_legaj = d21.nro_legaj
            JOIN mapuche.dh22 d22 ON d21.nro_liqui = d22.nro_liqui
            WHERE d21.codn_conce = :codn_conce
        ";
    }
}
