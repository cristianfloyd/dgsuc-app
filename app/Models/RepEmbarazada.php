<?php

declare(strict_types=1);

namespace App\Models;

use App\Data\RepEmbarazadaData;
use App\Services\EncodingService;
use App\Services\RepEmbarazadaService;
use App\Traits\HasUacadScope;
use App\Traits\MapucheConnectionTrait;
use Database\Factories\RepEmbarazadaFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Modelo para la gestión de embarazadas.
 *
 * Este modelo representa una tabla que se llena con datos provenientes de una consulta SQL
 * que extrae información de las tablas dh21h, dh03 y dh01 de la base Mapuche.
 * La tabla se encuentra en el schema 'suc' y contiene datos de personal en licencia por embarazo.
 *
 * Tablas relacionadas en Mapuche:
 * - dh21h: Licencias (codn_conce = 126 para embarazo)
 * - dh01: Datos personales
 * - dh03: Datos de unidad académica
 *
 * @property int $nro_legaj Número de legajo
 * @property string $apellido Apellido (CHAR 20)
 * @property string $nombre Nombre (CHAR 20)
 * @property string $cuil CUIL
 * @property string $codc_uacad Código de unidad académica (CHAR 4)
 *
 * @method static Builder|static query()
 */
class RepEmbarazada extends Model
{
    /** @use HasFactory<RepEmbarazadaFactory> */
    use HasFactory;

    use HasUacadScope;
    use MapucheConnectionTrait;

    /**
     * La clave primaria no es auto-incremental.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * Deshabilitar timestamps de Laravel.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Nombre de la tabla en PostgreSQL.
     *
     * @var string
     */
    protected $table = 'suc.rep_embarazadas';

    /**
     * Clave primaria del modelo.
     *
     * @var string
     */
    protected $primaryKey = 'nro_legaj';

    /**
     * Atributos que son asignables masivamente.
     *
     * @var array<string>
     */
    protected $fillable = [
        'nro_legaj',
        'apellido',
        'nombre',
        'cuil',
        'codc_uacad',
    ];

    public static function populateFromMapuche(): bool
    {
        return resolve(RepEmbarazadaService::class)->populateTable();
    }

    public static function truncateTable(): void
    {
        resolve(RepEmbarazadaService::class)->truncateTable();
    }

    /**
     * Convertir el modelo a un Data Object.
     */
    public function toData(): RepEmbarazadaData
    {
        return RepEmbarazadaData::from($this);
    }

    #[\Override]
    protected static function booted(): void
    {
        static::checktable();
    }

    protected static function checkTable(): void
    {
        resolve(RepEmbarazadaService::class)->ensureTableExists();
    }

    /**
     * Accessor para asegurar que apellido y nombre estén correctamente formateados.
     */
    protected function apellido(): Attribute
    {
        return Attribute::make(
            get: fn(string $value): ?string => EncodingService::toUtf8(trim($value)),
            set: fn(string $value): string => str_pad(substr($value, 0, 20), 20),
        );
    }

    protected function nombre(): Attribute
    {
        return Attribute::make(
            get: fn(string $value): ?string => EncodingService::toUtf8(trim($value)),
            set: fn(string $value): string => str_pad(substr($value, 0, 20), 20),
        );
    }
    /**
     * Casteos de atributos.
     *
     * @return array<string, string>
     */
    #[\Override]
    protected function casts(): array
    {
        return [
            'nro_legaj' => 'integer',
            'apellido' => 'string',
            'nombre' => 'string',
            'cuil' => 'string',
            'codc_uacad' => 'string',
        ];
    }
}
