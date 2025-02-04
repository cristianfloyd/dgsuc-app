<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\HasUacadScope;
use App\Data\RepEmbarazadaData;
use App\Services\EncodingService;
use App\Services\RepEmbarazadaService;
use App\Traits\MapucheConnectionTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;

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
 * @property int    $nro_legaj    Número de legajo
 * @property string $apellido     Apellido (CHAR 20)
 * @property string $nombre       Nombre (CHAR 20)
 * @property string $cuil         CUIL
 * @property string $codc_uacad   Código de unidad académica (CHAR 4)
 *
 * @method static \Illuminate\Database\Eloquent\Builder|static query()
 */
class RepEmbarazada extends Model
{
    /** @use HasFactory<\Database\Factories\RepEmbarazadaFactory> */
    use HasFactory;
    use HasUacadScope;
    use MapucheConnectionTrait;

    /**
     * Nombre de la tabla en PostgreSQL.
     *
     * @var string
     */
    protected $table = 'suc.rep_embarazadas';

    /**
     * La clave primaria no es auto-incremental.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * Clave primaria del modelo.
     *
     * @var string
     */
    protected $primaryKey = 'nro_legaj';

    /**
     * Deshabilitar timestamps de Laravel.
     *
     * @var bool
     */
    public $timestamps = false;

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

    /**
     * Casteos de atributos.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'nro_legaj' => 'integer',
        'apellido' => 'string',
        'nombre' => 'string',
        'cuil' => 'string',
        'codc_uacad' => 'string',
    ];

    protected static function booted(): void
    {
        static::checktable();
    }

    protected static function checkTable(): void
    {
        app(RepEmbarazadaService::class)->ensureTableExists();
    }

    public static function populateFromMapuche(): bool
    {
        return app(RepEmbarazadaService::class)->populateTable();
    }

    public static function truncateTable(): void
    {
        app(RepEmbarazadaService::class)->truncateTable();
    }

    /**
     * Convertir el modelo a un Data Object.
     *
     * @return RepEmbarazadaData
     */
    public function toData(): RepEmbarazadaData
    {
        return RepEmbarazadaData::from($this);
    }

    /**
     * Accessor para asegurar que apellido y nombre estén correctamente formateados.
     */
    protected function apellido(): Attribute
    {
        return Attribute::make(
            get: fn (string $value) => EncodingService::toUtf8(trim($value)),
            set: fn (string $value) => str_pad(substr($value, 0, 20), 20)
        );
    }

    protected function nombre(): Attribute
    {
        return Attribute::make(
            get: fn (string $value) => EncodingService::toUtf8(trim($value)),
            set: fn (string $value) => str_pad(substr($value, 0, 20), 20)
        );
    }
}
