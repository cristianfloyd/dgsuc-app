<?php

namespace App\Models;

use App\Traits\MapucheConnectionTrait;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ControlAportesDiferencia extends Model
{
    use MapucheConnectionTrait;

    public $timestamps = true;

    protected $table = 'suc.control_aportes_diferencias';

    protected $fillable = [
        'cuil',
        'codc_uacad',
        'caracter',
        'aportesijpdh21',
        'aporteinssjpdh21',
        'contribucionsijpdh21',
        'contribucioninssjpdh21',
        'aportesijp',
        'aporteinssjp',
        'contribucionsijp',
        'contribucioninssjp',
        'diferencia',
        'fecha_control',
        'connection',
    ];

    protected $casts = [
        'aportesijpdh21' => 'decimal:2',
        'aporteinssjpdh21' => 'decimal:2',
        'contribucionsijpdh21' => 'decimal:2',
        'contribucioninssjpdh21' => 'decimal:2',
        'aportesijp' => 'decimal:2',
        'aporteinssjp' => 'decimal:2',
        'contribucionsijp' => 'decimal:2',
        'contribucioninssjp' => 'decimal:2',
        'diferencia' => 'decimal:2',
    ];

    protected $appends = [
        'nro_cuil',
    ];

    public function nroCuil(): Attribute
    {
        return Attribute::make(
            get: function () {
                // Asegúrate de que `cuil` no sea null antes de intentar extraer `nro_cuil`
                if ($this->cuil) {
                    // Extrae los 8 dígitos del medio de `cuil`
                    return (int)(substr($this->cuil, 2, 8));
                }
                return null;
            },
        );
    }

    // ###############################################################
    // ######################## RELACIONES ###########################
    // ###############################################################
    public function sicossCalculo(): BelongsTo
    {
        return $this->belongsTo(AfipMapucheSicossCalculo::class, 'cuil', 'cuil');
    }

    public function mapucheSicoss(): BelongsTo
    {
        return $this->belongsTo(AfipMapucheSicoss::class, 'cuil', 'cuil');
    }

    public function relacionActiva(): BelongsTo
    {
        return $this->belongsTo(AfipRelacionesActivas::class, 'cuil', 'cuil');
    }

    public function dh01(): BelongsTo
    {
        return $this->belongsTo(Dh01::class, 'nro_cuil', 'nro_cuil');
    }

    public function cargos()
    {
        return $this->hasOneThrough(
            Dh03::class,
            Dh01::class,
            'nro_cuil',          // Clave foránea en Dh01
            'nro_legaj',        // Clave foránea en Dh03
            'nro_cuil',          // Clave local en ControlAportesDiferencia
            'nro_legaj',    // Clave local en Dh01
        );
    }

    /**
     * Obtiene el total de aportes DH21.
     */
    public function totalAportesDh21(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->aportesijpdh21 + $this->aporteinssjpdh21,
        );
    }

    /**
     * Obtiene el total de contribuciones DH21.
     */
    public function totalContribucionesDh21(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->contribucionsijpdh21 + $this->contribucioninssjpdh21,
        );
    }

    /**
     * Obtiene el total de aportes SICOSS.
     */
    public function totalAportesSicoss(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->aportesijp + $this->aporteinssjp,
        );
    }

    /**
     * Obtiene el total de contribuciones SICOSS.
     */
    public function totalContribucionesSicoss(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->contribucionsijp + $this->contribucioninssjp,
        );
    }
}
