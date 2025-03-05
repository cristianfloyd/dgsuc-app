<?php

namespace App\Models;

use App\Traits\MapucheConnectionTrait;
use Illuminate\Database\Eloquent\Model;
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
            return $query->whereIn('codn_conce', ['201', '202', '203', '204', '205', '247', '248']);
        } elseif ($tipo === 'contribuciones') {
            return $query->whereIn('codn_conce', ['301', '302', '303', '304', '305', '306', '307', '308', '347', '348']);
        }

        return $query;
    }
}
