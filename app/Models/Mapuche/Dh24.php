<?php

declare(strict_types=1);

namespace App\Models\Mapuche;

use App\Models\Dh03;
use App\Traits\Mapuche\HasAsignacionPresupuestaria;
use App\Traits\MapucheConnectionTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modelo para imputaciones presupuestarias por cargo
 *
 * @property int $nro_cargo
 * @property int $codn_progr Programa
 * @property int $codn_subpr SubPrograma
 * @property int $codn_proye Proyecto
 * @property int $codn_activ Actividad
 * @property int $codn_obra Obra
 * @property int $codn_fuent Fuente de fondos
 * @property float|null $porc_ipres
 * @property int $codn_area Unidad
 * @property int $codn_subar Sub Unidad
 * @property int $codn_final Finalidad
 * @property int $codn_funci
 * @property int $codn_grupo_presup
 * @property string $tipo_ejercicio
 * @property int $codn_subsubar Sub Sub Unidad
 * @method static create(array $toArray)
 * @method static find(int|null $editingId)
 */
class Dh24 extends Model
{
    use HasFactory;
    use MapucheConnectionTrait;
    use HasAsignacionPresupuestaria;

    /**
     * Indica si el modelo debe tener timestamps
     */
    public $timestamps = false;
    /**
     * Indica si la clave primaria es auto-incrementable
     */
    public $incrementing = false;
    /**
     * Nombre de la tabla en la base de datos
     */
    protected $table = 'dh24';
    /**
     * La clave primaria compuesta del modelo
     */
    protected $primaryKey = [
        'nro_cargo', 'codn_area', 'codn_subar', 'codn_progr',
        'codn_subpr', 'codn_proye', 'codn_activ', 'codn_obra',
        'codn_fuent', 'codn_final', 'codn_funci',
        'codn_grupo_presup', 'tipo_ejercicio', 'codn_subsubar'
    ];
    /**
     * Atributos que son asignables en masa
     */
    protected $fillable = [
        'nro_cargo', 'codn_progr', 'codn_subpr', 'codn_proye',
        'codn_activ', 'codn_obra', 'codn_fuent', 'porc_ipres',
        'codn_area', 'codn_subar', 'codn_final', 'codn_funci',
        'codn_grupo_presup', 'tipo_ejercicio', 'codn_subsubar'
    ];

    /**
     * Conversión de tipos de atributos
     */
    protected $casts = [
        'nro_cargo' => 'integer',
        'codn_progr' => 'integer',
        'codn_subpr' => 'integer',
        'codn_proye' => 'integer',
        'codn_activ' => 'integer',
        'codn_obra' => 'integer',
        'codn_fuent' => 'integer',
        'porc_ipres' => 'decimal:2',
        'codn_area' => 'integer',
        'codn_subar' => 'integer',
        'codn_final' => 'integer',
        'codn_funci' => 'integer',
        'codn_grupo_presup' => 'integer',
        'tipo_ejercicio' => 'string',
        'codn_subsubar' => 'integer'
    ];

    /**
     * Relación con el cargo
     */
    public function cargo(): BelongsTo
    {
        return $this->belongsTo(Dh03::class, 'nro_cargo', 'nro_cargo');
    }

    /**
     * Scope para filtrar por tipo de ejercicio activo
     */
    public function scopeActivo($query)
    {
        return $query->where('tipo_ejercicio', 'A');
    }
}
