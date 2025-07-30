<?php

namespace App\Models\Mapuche;

use App\Services\EncodingService;
use App\Traits\MapucheConnectionTrait;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $nombre_seccion Section Name
 * @property string $nombre_parametro Parameter Name
 * @property string|null $dato_parametro Parameter Value
 */
class Rrhhini extends Model
{
    use MapucheConnectionTrait;

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    protected $table = 'rrhhini';

    protected $schema = 'mapuche';

    /**
     * The primary key for the model.
     * @var array
     */
    protected $primaryKey = ['nombre_seccion', 'nombre_parametro']; // @phpstan-ignore property.phpDocType

    /**
     * The attributes that are mass assignable.
     * @var array<string>
     * @phpstan-ignore property.phpDocType
     */
    protected $fillable = [
        'nombre_seccion',
        'nombre_parametro',
        'dato_parametro',
    ];

    /**
     * Get parameter value by section and parameter name.
     *
     * @param string $section
     * @param string $parameter
     *
     * @return string|null
     */
    public static function getParameterValue($section, $parameter)
    {
        return static::where('nombre_seccion', $section)
            ->where('nombre_parametro', $parameter)
            ->value('dato_parametro');
    }

    /**
     * Get the route key for the model.
     */
    #[\Override]
    public function getRouteKeyName(): string
    {
        return 'nombre_seccion'; // Utilizamos uno de los campos de la clave compuesta como identificador principal
    }

    /**
     * Retrieve the model for a bound value.
     *
     * @param mixed $value
     * @param string|null $field
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    #[\Override]
    public function resolveRouteBinding($value, $field = null)
    {
        return $this->where('nombre_seccion', $value)
            ->where('nombre_parametro', request()->route('nombre_parametro'))
            ->first();
    }

    /**
     * Get the value of the model's primary key.
     * This is required for Filament to work with composite keys.
     */
    #[\Override]
    public function getKey(): string
    {
        return $this->nombre_seccion . '|' . $this->nombre_parametro;
    }

    /**
     * Get the value of the model's route key.
     */
    #[\Override]
    public function getRouteKey(): string
    {
        return $this->getKey();
    }

    /**
     * Decode the composite key.
     */
    public static function decodeKey(string $key): array
    {
        return explode('|', $key);
    }

    /**
     * Find a model by its primary key.
     *
     * @param string|int $key
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public static function find($key)
    {
        if (\is_string($key) && str_contains($key, '|')) {
            [$seccion, $parametro] = static::decodeKey($key);
            return static::where('nombre_seccion', $seccion)
                ->where('nombre_parametro', $parametro)
                ->first();
        }

        return null;
    }

    protected function nombreSeccion(): Attribute
    {
        return Attribute::make(
            get: fn ($value): ?string => EncodingService::toUtf8($value),
            set: fn ($value): ?string => EncodingService::toLatin1($value),
        );
    }

    protected function nombreParametro(): Attribute
    {
        return Attribute::make(
            get: fn ($value): ?string => EncodingService::toUtf8($value),
            set: fn ($value): ?string => EncodingService::toLatin1($value),
        );
    }

    protected function datoParametro(): Attribute
    {
        return Attribute::make(
            get: fn ($value): ?string => EncodingService::toUtf8($value),
            set: fn ($value): ?string => EncodingService::toLatin1($value),
        );
    }
}
