<?php

namespace App\Models\Reportes;

use Carbon\Carbon;
use App\Models\Dh03;
use App\Enums\LegajoCargo;
use App\Enums\BloqueosEstadoEnum;
use App\Traits\MapucheConnectionTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BloqueosDataModel extends Model
{
    use MapucheConnectionTrait, HasFactory;
    protected $table = 'suc.rep_bloqueos_import';
    protected $primaryKey = 'id';

    protected $fillable = [
        'id',
        'nro_liqui',
        'fecha_registro',
        'email',
        'nombre',
        'usuario_mapuche',
        'dependencia',
        'nro_legaj',
        'nro_cargo',
        'fecha_baja',
        'tipo',
        'observaciones',
        'chkstopliq',
        'estado',
        'mensaje_error',
    ];

    protected $casts = [
        'fecha_registro' => 'datetime',
        'fecha_baja' => 'date:Y-m-d',
        'chkstopliq' => 'boolean',
        'fec_baja' => 'date:Y-m-d',
        'estado' => BloqueosEstadoEnum::class,
    ];

    protected static function boot()
    {
        parent::boot();
        self::creating(function ($model) {
            $model->estado = BloqueosEstadoEnum::IMPORTADO;
        });
    }

    public function validarEstado(): void
    {
        if (Dh03::validarParLegajoCargo($this->nro_legaj, $this->nro_cargo)) {
            $this->estado = BloqueosEstadoEnum::VALIDADO;
        } else {
            $this->estado = BloqueosEstadoEnum::ERROR_VALIDACION;
            $this->mensaje_error = 'Par legajo-cargo no encontrado';
        }
        $this->save();
    }

    /* ######## ATTRIBUTES ########################################## */
    public function legajoCargo(): Attribute
    {
        return Attribute::make(
            get: fn() => LegajoCargo::from($this->nro_legaj, $this->nro_cargo),
        );
    }

    public function fechaBaja(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value ? Carbon::parse($value)->format('Y-m-d') : null,
            set: fn ($value) => $value ? Carbon::parse($value)->format('Y-m-d') : null,
        );
    }

    /* ######## ACCESORS ########################################### */
    public function getFechasCoincidentesAttribute(): bool
    {
        if (!$this->fecha_baja || !$this->cargo?->fec_baja) {
            return false;
        }

        return Carbon::parse($this->fecha_baja)->format('Y-m-d') ===
            Carbon::parse($this->cargo->fec_baja)->format('Y-m-d');
    }

    /* ##############################################################
    ####  RELACIONES ############################################### */

    public function cargo(): BelongsTo
    {
        return $this->belongsTo(Dh03::class, 'nro_cargo', 'nro_cargo');
    }

    /* ################## SCOPES ##################################### */
    public function scopeFechasCoinciden($query)
    {
        return $query->whereRaw('DATE(fecha_baja) = DATE(fec_baja)');
    }

    public function scopeTipo($query, $tipo)
    {
        return $query->where('tipo', strtolower($tipo));
    }
}
