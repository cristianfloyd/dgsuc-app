<?php

namespace App\Models;

use App\Traits\MapucheConnectionTrait;
use Illuminate\Database\Eloquent\Model;

class ComprobanteNominaModel extends Model
{
    use MapucheConnectionTrait;

    protected $table = 'suc.comprobantes_nomina';

    public $timestamps = true;

    protected $fillable = [
        'anio_periodo',
        'mes_periodo',
        'numero_liquidacion',
        'descripcion_liquidacion',
        'tipo_pago',
        'importe_neto',
        'area_administrativa',
        'subarea_administrativa',
        'numero_retencion',
        'descripcion_retencion',
        'importe_retencion',
        'requiere_cheque',
        'codigo_grupo'
    ];

    protected $casts = [
        'anio_periodo' => 'integer',
        'mes_periodo' => 'integer',
        'numero_liquidacion' => 'integer',
        'importe_neto' => 'decimal:2',
        'importe_retencion' => 'decimal:2',
        'requiere_cheque' => 'boolean'
    ];
}
