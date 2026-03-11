<?php

namespace App\Models;

use App\Traits\MapucheConnectionTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Override;

class ControlConceptosPeriodo extends Model
{
    use HasFactory;
    use MapucheConnectionTrait;

    public $timestamps = true;

    protected $primaryKey = 'id';

    protected $table = 'control_conceptos_periodos';

    protected $schema = 'suc';

    protected $fillable = [
        'year',
        'month',
        'codn_conce',
        'desc_conce',
        'importe',
        'connection_name',
    ];

    protected $appends = [
        'tipo_concepto',
        'es_aporte',
        'es_contribucion',
    ];

    /**
     * Obtiene el concepto relacionado de la tabla DH12.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Dh12, $this>
     */
    public function dh12(): BelongsTo
    {
        return $this->belongsTo(Dh12::class, 'codn_conce', 'codn_conce')
            ->withoutGlobalScopes(); // Para asegurar que no se apliquen scopes globales que puedan afectar la relación
    }

    /**
     * Determina si el concepto es de tipo Aporte.
     */
    protected function esAporte(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return \Illuminate\Database\Eloquent\Casts\Attribute::make(get: fn(): bool => str_starts_with($this->codn_conce, '2'));
    }

    /**
     * Determina si el concepto es de tipo Contribución.
     */
    protected function esContribucion(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return \Illuminate\Database\Eloquent\Casts\Attribute::make(get: fn(): bool => str_starts_with($this->codn_conce, '3'));
    }

    /**
     * Obtiene el tipo de concepto como string.
     */
    protected function getTipoConceptoAttribute(): string
    {
        return match (substr($this->codn_conce, 0, 1)) {
            '2' => 'Aportes',
            '3' => 'Contribuciones',
            default => 'Otro',
        };
    }

    /**
     * Scope para filtrar por período fiscal.
     */
    #[\Illuminate\Database\Eloquent\Attributes\Scope]
    protected function periodoFiscal($query, int $year, int $month)
    {
        return $query->where('year', $year)->where('month', $month);
    }

    /**
     * Scope para filtrar por concepto.
     */
    #[\Illuminate\Database\Eloquent\Attributes\Scope]
    protected function concepto($query, string $codn_conce)
    {
        return $query->where('codn_conce', $codn_conce);
    }

    /**
     * Scope para agrupar conceptos por tipo (aportes o contribuciones).
     */
    #[\Illuminate\Database\Eloquent\Attributes\Scope]
    protected function tipoConcepto($query, string $tipo)
    {
        if ($tipo === 'aportes') {
            return $query->whereIn('codn_conce', ['201', '202', '203', '204', '205', '247', '248', '403']);
        }
        if ($tipo === 'contribuciones') {
            return $query->whereIn('codn_conce', ['301', '302', '303', '304', '305', '306', '307', '308', '347', '348', '447']);
        }

        return $query;
    }

    #[Override]
    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'month' => 'integer',
            'importe' => 'decimal:2',
        ];
    }
}
