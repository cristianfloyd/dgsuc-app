<?php

namespace App\Models;

use App\Models\Dh12;
use App\Enums\TipoConce;
use App\Traits\MapucheConnectionTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ControlConceptosPeriodo extends Model
{
    use HasFactory, MapucheConnectionTrait;

    protected $primaryKey = 'id';
    protected $table = 'control_conceptos_periodos';
    protected $schema = 'suc';
    public $timestamps = true;

    protected $fillable = [
        'year',
        'month',
        'codn_conce',
        'desc_conce',
        'importe',
        'connection_name',
    ];

    protected $casts = [
        'year' => 'integer',
        'month' => 'integer',
        'importe' => 'decimal:2',
    ];

    protected $appends = [
        'tipo_concepto',
        'es_aporte',
        'es_contribucion'
    ];

    /**
     * Obtiene el concepto relacionado de la tabla DH12
     */
    public function dh12(): BelongsTo
    {
        return $this->belongsTo(Dh12::class, 'codn_conce', 'codn_conce')
            ->withoutGlobalScopes(); // Para asegurar que no se apliquen scopes globales que puedan afectar la relación
    }

    /**
     * Determina si el concepto es de tipo Aporte
     */
    public function getEsAporteAttribute(): bool
    {
        return str_starts_with($this->codn_conce, '2');
    }

    /**
     * Determina si el concepto es de tipo Contribución
     */
    public function getEsContribucionAttribute(): bool
    {
        return str_starts_with($this->codn_conce, '3');
    }

    /**
     * Obtiene el tipo de concepto como string
     */
    public function getTipoConceptoAttribute(): string
    {
        return match (substr($this->codn_conce, 0, 1)) {
            '2' => 'Aportes',
            '3' => 'Contribuciones',
            default => 'Otro'
        };
    }

    /**
     * Scope para filtrar por período fiscal
     */
    public function scopePeriodoFiscal($query, int $year, int $month)
    {
        return $query->where('year', $year)->where('month', $month);
    }

    /**
     * Scope para filtrar por concepto
     */
    public function scopeConcepto($query, string $codn_conce)
    {
        return $query->where('codn_conce', $codn_conce);
    }

    /**
     * Scope para agrupar conceptos por tipo (aportes o contribuciones)
     */
    public function scopeTipoConcepto($query, string $tipo)
    {
        if ($tipo === 'aportes') {
            return $query->whereIn('codn_conce', ['201', '202', '203', '204', '205', '247', '248', '403']);
        } elseif ($tipo === 'contribuciones') {
            return $query->whereIn('codn_conce', ['301', '302', '303', '304', '305', '306', '307', '308', '347', '348', '447']);
        }

        return $query;
    }
}
