<?php

namespace App\Models;

use App\Traits\MapucheConnectionTrait;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Override;

/**
 * Modelo para la tabla de Acumuladores del sistema Mapuche.
 *
 * @property int $nro_acumu Número de Acumulador (Primary Key)
 * @property string|null $observacion Observaciones del campo (200)
 * @property string|null $desc_acumu Descripción Acumulador (15)
 */
class Dh14 extends Model
{
    use HasFactory;
    use MapucheConnectionTrait;

    /**
     * Indica si el modelo debe ser timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Nombre de la tabla en la base de datos.
     *
     * @var string
     */
    protected $table = 'dh14';

    /**
     * Clave primaria del modelo.
     *
     * @var string
     */
    protected $primaryKey = 'nro_acumu';

    /**
     * Los atributos que son asignables en masa.
     *
     * @var array<string>
     */
    protected $fillable = [
        'nro_acumu',
        'observacion',
        'desc_acumu',
    ];

    /**
     * Obtiene los conceptos (Dh12) que utilizan este acumulador.
     * La relación se establece a través del campo flag_acumu de Dh12.
     */
    public function conceptos()
    {
        return Dh12::query()->where('flag_acumu', 'like', DB::raw("concat(repeat('_', ? - 1), 'S', '%')"));
    }

    /**
     * Accessor para limpiar los espacios en blanco del campo desc_acumu.
     */
    protected function descAcumu(): Attribute
    {
        return Attribute::make(
            get: fn($value): ?string => $value ? trim((string) $value) : null,
        );
    }

    /**
     * Los atributos que deben ser convertidos a tipos nativos.
     *
     * @return array<string, string>
     */
    #[Override]
    protected function casts(): array
    {
        return [
            'nro_acumu' => 'integer',
            'observacion' => 'string',
            'desc_acumu' => 'string',
        ];
    }
}
