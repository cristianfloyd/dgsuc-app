<?php

namespace App\Models\Mapuche\Catalogo;

use App\Models\Dh03;
use App\Services\EncodingService;
use App\Traits\MapucheConnectionTrait;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

/**
 * Modelo Eloquent para la tabla 'mapuche.dh36' que representa los datos de dependencias de empleados.
 *
 * @property string $coddependesemp Código único de la dependencia del empleado.
 * @property string $cordinadorcontrato Coordinador del contrato.
 * @property string $descdependesemp Descripción de la dependencia del empleado.
 * @property string $cod_organismo Código del organismo.
 * @property string $cod_organismo_eval Código del organismo evaluador.
 * @property string $cod_ubic_geografica_sirhu Código de la ubicación geográfica en el sistema SIRHU.
 * @property \App\Models\Mapuche\Catalogo\Dhe4 $organismo Relación con el modelo Dhe4 (organismo).
 * @property \App\Models\Mapuche\Catalogo\Dhe4 $organismoEvaluador Relación con el modelo Dhe4 (organismo evaluador).
 */
class Dh36 extends Model
{
    use MapucheConnectionTrait;

    public $incrementing = false;

    public $timestamps = false;

    protected $table = 'dh36';

    protected $primaryKey = 'coddependesemp';

    protected $keyType = 'string';

    protected $fillable = [
        'coddependesemp',
        'cordinadorcontrato',
        'descdependesemp',
        'cod_organismo',
        'cod_organismo_eval',
        'cod_ubic_geografica_sirhu',
    ];

    protected array $encodedFields = [
        'descdependesemp',
    ];

    #[\Override]
    public static function boot(): void
    {
        parent::boot();

        // Establecer codificación SQL_ASCII para la conexión
        DB::statement("SET client_encoding TO 'SQL_ASCII'");

        static::retrieved(function ($model): void {
            if (isset($model->descdependesemp)) {
                $model->descdependesemp = EncodingService::toUtf8($model->descdependesemp);
            }
        });
    }

    public function scopeWithoutEncoding($query)
    {
        return $query->whereRaw("encode(descdependesemp::bytea, 'escape') IS NOT NULL");
    }

    /**
     * Relación con el modelo Dhe4 (organismo).
     */
    public function organismo(): BelongsTo
    {
        return $this->belongsTo(Dhe4::class, 'cod_organismo', 'cod_organismo');
    }

    /**
     * Relación con el modelo Dhe4 (organismo evaluador).
     */
    public function organismoEvaluador(): BelongsTo
    {
        return $this->belongsTo(Dhe4::class, 'cod_organismo_eval', 'cod_organismo');
    }

    public function cargos(): HasMany
    {
        return $this->hasMany(Dh03::class, 'coddependesemp', 'coddependesemp');
    }

    public function dhe4(): BelongsTo
    {
        return $this->belongsTo(Dhe4::class, 'cod_organismo', 'cod_organismo');
    }

    /* ################### ACCESSORS Y MUTATORS ################### */
    protected function descdependesemp(): Attribute
    {
        return Attribute::make(
            get: fn (string $value): ?string => EncodingService::toUtf8($value),
            set: fn (string $value): ?string => EncodingService::toLatin1($value),
        );
    }

    protected function casts(): array
    {
        return [
            'descdependesemp' => 'string',
        ];
    }
}
