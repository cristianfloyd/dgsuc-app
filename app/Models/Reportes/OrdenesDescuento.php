<?php

namespace App\Models\Reportes;

use App\Traits\MapucheConnectionTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OrdenesDescuento extends Model
{
    use HasFactory, MapucheConnectionTrait;

    protected $table = 'OrdenesDescuento';

    /**
     * Los atributos que son asignables masivamente
     */
    protected $fillable = [
        'nro_liqui',
        'desc_liqui',
        'codc_uacad',
        'codc_uacad',
        'leyenda_sub_dependencia',
        'id_dependencia_de_pago',
        'descripcion_dep_pago',
        'codn_funci',
        'id_beneficiario',
        'descripcion_beneficiario',
        'clase_liquidacion',
        'id_fuente_financiamiento',
        'inciso',
        'id_programa',
        'programa_descripcion',
        'id_programa_de_pago',
        'descripcion_prog_pago',
        'numero',
        'fecha_emision',
        'anio',
        'periodo_fiscal', // dh22 -> periodo_fiscal
        'id_tipo_orden_pago',
        'codn_conce',   // dh21
        'desc_conce',  // dh12
        'impp_conce',
        'total_imp_nd',
    ];

    /**
     * Los atributos que deben ser convertidos a tipos nativos
     */
    protected $casts = [
        'fecha_emision' => 'datetime',
        'total_imp_def' => 'decimal:2',
        'total_imp_nd' => 'decimal:2',
    ];
}
