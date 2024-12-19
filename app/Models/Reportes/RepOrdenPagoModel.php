<?php

namespace App\Models\Reportes;

use App\Models\Mapuche\Catalogo\Dh30;
use App\Traits\MapucheConnectionTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RepOrdenPagoModel extends Model
{
    use MapucheConnectionTrait, HasFactory;

    const int TABLA_UNIDAD_ACADEMICA = 13;

    /**
     * Indica que el modelo RepOrdenPago tiene campos de fecha de creación y actualización.
     */
    public $timestamps = true;

    /**
     * Tabla de la base de datos utilizada por el modelo RepOrdenPago.
     */
    protected $table = 'suc.rep_orden_pago';
    /**
     * Los atributos que se pueden rellenar masivamente.
     * @var array
     */
    protected $fillable = [
        'nro_liqui',
        'banco',
        'codn_funci',
        'codn_fuent',
        'codc_uacad',
        'caracter',
        'codn_progr',
        'remunerativo',
        'no_remunerativo',
        'descuentos',
        'aportes',
        'sueldo',
        'estipendio',
        'med_resid',
        'productividad',
        'sal_fam',
        'hs_extras',
        'total',
    ];

    /**
     * Indica los tipos de datos que deben ser convertidos automáticamente al acceder a los atributos del modelo.
     * @var array
     */
    protected $casts = [
        'nro_liqui' => 'integer',
        'banco' => 'integer',
        'remunerativo' => 'decimal:2',
        'no_remunerativo' => 'decimal:2',
        'descuentos' => 'decimal:2',
        'aportes' => 'decimal:2',
        'sueldo' => 'decimal:2',
        'estipendio' => 'decimal:2',
        'med_resid' => 'decimal:2',
        'productividad' => 'decimal:2',
        'sal_fam' => 'decimal:2',
        'hs_extras' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function unidadAcademica(): BelongsTo
    {
        return $this->belongsTo(Dh30::class, 'codc_uacad', 'desc_abrev')
            ->where('nro_tabla', self::TABLA_UNIDAD_ACADEMICA);
    }
}
