<?php

namespace App\Models;

use App\Traits\MapucheConnectionTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ControlContribucionesDiferencia extends Model
{
    use MapucheConnectionTrait;

    protected $table = 'suc.control_contribuciones_diferencias';
    public $timestamps = true;

    protected $fillable = [
        'cuil',
        'codc_uacad',
        'caracter',
        'nro_legaj',
        'contribucionsijpdh21',
        'contribucioninssjpdh21',
        'contribucionsijp',
        'contribucioninssjp',
        'diferencia',
        'fecha_control',
        'connection'
    ];

    protected $casts = [
        'contribucionsijpdh21' => 'decimal:2',
        'contribucioninssjpdh21' => 'decimal:2',
        'contribucionsijp' => 'decimal:2',
        'contribucioninssjp' => 'decimal:2',
        'diferencia' => 'decimal:2',
        'fecha_control' => 'datetime'
    ];

    protected $appends = [
        'nro_cuil'
    ];

    public function nroCuil(): Attribute
    {
        return Attribute::make(
            get: function () {
                // Asegúrate de que `cuil` no sea null antes de intentar extraer `nro_cuil`
                if ($this->cuil) {
                    // Extrae los 8 dígitos del medio de `cuil`
                    return intval(substr($this->cuil, 2, 8));
                }
            return null;
            }
        );
    }

    // ################################################
    // ################## RELACIONES ##################
    // ################################################
    public function sicossCalculo(): BelongsTo
    {
        return $this->belongsTo(AfipMapucheSicossCalculo::class, 'cuil', 'cuil');
    }

    public function relacionActiva(): BelongsTo
    {
        return $this->belongsTo(AfipRelacionesActivas::class, 'cuil', 'cuil');
    }

    public function dh01(): BelongsTo
    {
        return $this->belongsTo(Dh01::class, 'nro_cuil', 'nro_cuil');
    }

    // ################################################
    // ################## ACCESORES ##################
    // ################################################
    
    /**
     * Calcula el total de contribuciones sumando los diferentes tipos de contribuciones.
     * 
     * @return Attribute Atributo calculado con la suma total de contribuciones
     */
    public function totalContribuciones(): Attribute
    {
        return Attribute::make(
            get: function () {
                return $this->contribucionsijpdh21 + $this->contribucioninssjpdh21 + $this->contribucionsijp + $this->contribucioninssjp;
            }
        );
    }
}

