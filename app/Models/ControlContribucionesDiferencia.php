<?php

namespace App\Models;

use App\Traits\MapucheConnectionTrait;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Override;

class ControlContribucionesDiferencia extends Model
{
    use MapucheConnectionTrait;

    public $timestamps = true;

    protected $table = 'suc.control_contribuciones_diferencias';

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
        'connection',
    ];

    protected $appends = [
        'nro_cuil',
    ];

    // ################################################
    // ################## RELACIONES ##################
    // ################################################
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\AfipMapucheSicossCalculo, $this>
     */
    public function sicossCalculo(): BelongsTo
    {
        return $this->belongsTo(AfipMapucheSicossCalculo::class, 'cuil', 'cuil');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\AfipRelacionesActivas, $this>
     */
    public function relacionActiva(): BelongsTo
    {
        return $this->belongsTo(AfipRelacionesActivas::class, 'cuil', 'cuil');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Dh01, $this>
     */
    public function dh01(): BelongsTo
    {
        return $this->belongsTo(Dh01::class, 'nro_cuil', 'nro_cuil');
    }

    protected function nroCuil(): Attribute
    {
        return Attribute::make(
            get: function (): ?int {
                // Asegúrate de que `cuil` no sea null antes de intentar extraer `nro_cuil`
                if ($this->cuil) {
                    // Extrae los 8 dígitos del medio de `cuil`
                    return (int) (substr($this->cuil, 2, 8));
                }
                return null;
            },
        );
    }

    // ################################################
    // ################## ACCESORES ##################
    // ################################################

    /**
     * Calcula el total de contribuciones sumando los diferentes tipos de contribuciones.
     *
     * @return Attribute Atributo calculado con la suma total de contribuciones
     */
    protected function totalContribuciones(): Attribute
    {
        return Attribute::make(
            get: fn(): float|int|array => $this->contribucionsijpdh21 + $this->contribucioninssjpdh21 + $this->contribucionsijp + $this->contribucioninssjp,
        );
    }

    #[Override]
    protected function casts(): array
    {
        return [
            'contribucionsijpdh21' => 'decimal:2',
            'contribucioninssjpdh21' => 'decimal:2',
            'contribucionsijp' => 'decimal:2',
            'contribucioninssjp' => 'decimal:2',
            'diferencia' => 'decimal:2',
            'fecha_control' => 'datetime',
        ];
    }
}
