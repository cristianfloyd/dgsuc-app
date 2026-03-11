<?php

namespace App\Models;

use App\Traits\MapucheConnectionTrait;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Override;

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

    protected $appends = [
        'nro_cuil',
    ];

    // ###############################################################
    // ######################## RELACIONES ###########################
    // ###############################################################
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\AfipMapucheSicossCalculo, $this>
     */
    public function sicossCalculo(): BelongsTo
    {
        return $this->belongsTo(AfipMapucheSicossCalculo::class, 'cuil', 'cuil');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\AfipMapucheSicoss, $this>
     */
    public function mapucheSicoss(): BelongsTo
    {
        return $this->belongsTo(AfipMapucheSicoss::class, 'cuil', 'cuil');
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

    /**
     * Obtiene el total de aportes DH21.
     */
    protected function totalAportesDh21(): Attribute
    {
        return Attribute::make(
            get: fn(): float => $this->aportesijpdh21 + $this->aporteinssjpdh21,
        );
    }

    /**
     * Obtiene el total de contribuciones DH21.
     */
    protected function totalContribucionesDh21(): Attribute
    {
        return Attribute::make(
            get: fn(): float => $this->contribucionsijpdh21 + $this->contribucioninssjpdh21,
        );
    }

    /**
     * Obtiene el total de aportes SICOSS.
     */
    protected function totalAportesSicoss(): Attribute
    {
        return Attribute::make(
            get: fn(): float => $this->aportesijp + $this->aporteinssjp,
        );
    }

    /**
     * Obtiene el total de contribuciones SICOSS.
     */
    protected function totalContribucionesSicoss(): Attribute
    {
        return Attribute::make(
            get: fn(): float => $this->contribucionsijp + $this->contribucioninssjp,
        );
    }

    #[Override]
    protected function casts(): array
    {
        return [
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
    }
}
