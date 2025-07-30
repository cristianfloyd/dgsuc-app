<?php

namespace App\Models;

use App\Models\Dh12;
use App\Services\EncodingService;
use Illuminate\Support\Facades\DB;
use App\Traits\MapucheConnectionTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use App\Traits\MapucheLiquiConnectionTrait;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modelo para la tabla mapuche.dh13
 *
 * @property int $codn_conce
 * @property string|null $desc_calcu
 * @property int $nro_orden_formula
 * @property string|null $desc_condi
 *
 * @property-read Dh12 $conceptoBase
 */
class Dh13 extends Model
{
    use MapucheConnectionTrait;

    private static $connectionInstance = null;

    protected static function getMapucheConnection()
    {
        if (self::$connectionInstance === null) {
            $model = new static();
            self::$connectionInstance = $model->getConnectionFromTrait();
        }
        return self::$connectionInstance;
    }

    /**
     * La tabla asociada con el modelo.
     *
     * @var string
     */
    protected $table = 'dh13';

    /**
     * Indica si el modelo debe ser timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Las claves primarias del modelo.
     *
     * @var array
     */
    protected $primaryKey = ['codn_conce', 'nro_orden_formula'];

    /**
     * Indica si la clave primaria es auto-incrementable.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * Los atributos que son asignables en masa.
     *
     * @var array
     */
    protected $fillable = [
        'codn_conce',
        'desc_calcu',
        'nro_orden_formula',
        'desc_condi',
    ];

    /**
     * Casting de atributos
     */
    protected $casts = [
        'codn_conce' => 'integer',
        'nro_orden_formula' => 'integer',
        'desc_calcu' => 'string',
        'desc_condi' => 'string'
    ];

    protected static function boot()
    {
        parent::boot();

        DB::statement("SET client_encoding TO 'SQL_ASCII'");

        static::retrieved(function ($model) {
            if (isset($model->attributes['desc_calcu'])) {
                $model->attributes['desc_calcu'] = EncodingService::toUtf8($model->attributes['desc_calcu']);
            }
            if (isset($model->attributes['desc_condi'])) {
                $model->attributes['desc_condi'] = EncodingService::toUtf8($model->attributes['desc_condi']);
            }
        });

        static::saving(function ($model) {
            if (isset($model->attributes['desc_calcu'])) {
                $model->attributes['desc_calcu'] = EncodingService::toLatin1($model->attributes['desc_calcu']);
            }
            if (isset($model->attributes['desc_condi'])) {
                $model->attributes['desc_condi'] = EncodingService::toLatin1($model->attributes['desc_condi']);
            }
        });
    }

    protected function descCalcu(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => EncodingService::toUtf8($value),
            set: fn ($value) => EncodingService::toLatin1($value)
        );
    }

    protected function descCondi(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => EncodingService::toUtf8($value),
            set: fn ($value) => EncodingService::toLatin1($value)
        );
    }

    /**
     * Obtiene el Dh12 asociado con este Dh13.
     */
    public function dh12(): BelongsTo
    {
        return $this->belongsTo(Dh12::class, 'codn_conce', 'codn_conce');
    }

    /* ############################################################## */
    /* ################# Metodos para PK compuesta ################## */
    /* ############################################################## */
    /**
     * Obtiene la clave única para el modelo.
     * Este método devuelve 'id' como nombre de la clave primaria.
     * Esto es necesario para que FilamentPHP pueda trabajar con el modelo.
     * @return string
     */
    public function getKeyName()
    {
        return 'id';
    }

    /**
     * Obtiene el valor de la clave única para el modelo.
     * devuelve una representación de cadena única de la clave primaria compuesta.
     * @return string
     */
    public function getKey(): string
    {
        return "{$this->codn_conce}-{$this->nro_orden_formula}";
    }

    /**
     * Establece la clave única para el modelo.
     *
     * @param  mixed  $key
     * @return void
     */
    public function setKeyName($key)
    {
        $this->primaryKey = $key;
    }
    /**
     * Obtiene el valor de la clave única para rutas.
     *
     * @return string
     */
    public function getRouteKeyName()
    {
        return 'id';
    }
    /**
     * Recupera el modelo por su clave única.
     *
     * @param  mixed  $key
     * @param  string|null  $field
     * @return Model|Collection|static[]|static|null
     */
    public function resolveRouteBinding($key, $field = null)
    {
        if ($field === 'id') {
            [$codn_conce, $nro_orden_formula] = explode('-', $key);
            return $this->where('codn_conce', $codn_conce)
                ->where('nro_orden_formula', $nro_orden_formula)
                ->first();
        }
        return parent::resolveRouteBinding($key, $field);
    }
    /**
     * Obtiene una nueva instancia de query para el modelo.
     *
     * @return Builder
     */
    public function newQuery()
    {
        return parent::newQuery()->addSelect(
            '*',
            DB::connection($this->getConnectionName())->raw("CONCAT(codn_conce, '-', nro_orden_formula) as id")
        )
        ->orderBy('codn_conce')
        ->orderBy('nro_orden_formula');
    }

    /**
     * Recupera un modelo por su clave única compuesta.
     *
     * @param string $id La clave única compuesta en el formato "codn_conce-nro_orden_formula".
     * @param array $columns Los campos a recuperar (por defecto, todos los campos).
     * @return Model|null El modelo encontrado, o null si no se encuentra.
     */
    public function find($id, $columns = ['*'])
    {
        [$codn_conce, $nro_orden_formula] = explode('-', $id);
        return $this->where('codn_conce', $codn_conce)
            ->where('nro_orden_formula', $nro_orden_formula)
            ->first($columns);
    }

    public function scopeDefaultOrder($query)
    {
        return $query->orderBy('codn_conce')->orderBy('nro_orden_formula');
    }


    public static function diagnosticarCodificacionConConcepto($codn_conce = 520)
    {
        $connection = static::getConnectionFromTrait();
        $connection->statement("SET client_encoding TO 'SQL_ASCII'");

        $registro = self::with('dh12')
            ->where('codn_conce', $codn_conce)
            ->first();

        dd([
            'dh13' => [
                'codn_conce' => $registro->codn_conce,
                'desc_calcu_raw' => $registro->getAttributes()['desc_calcu'],
                'desc_calcu_hex' => bin2hex($registro->getAttributes()['desc_calcu']),
                'desc_condi_raw' => $registro->getAttributes()['desc_condi'],
                'desc_condi_hex' => bin2hex($registro->getAttributes()['desc_condi'])
            ],
            'dh12' => [
                'desc_conce_raw' => $registro->dh12->getAttributes()['desc_conce'],
                'desc_conce_utf8' => EncodingService::toUtf8($registro->dh12->getAttributes()['desc_conce']),
                'desc_conce_hex' => bin2hex($registro->dh12->getAttributes()['desc_conce'])
            ],
            'configuracion_db' => [
                'connection_name' => $connection->getConfig('name'),
                'client_encoding' => $connection->selectOne("SHOW client_encoding")->client_encoding,
                'server_encoding' => $connection->selectOne("SHOW server_encoding")->server_encoding,
                'server_collation' => $connection->selectOne("SHOW lc_collate")->lc_collate,
                'server_ctype' => $connection->selectOne("SHOW lc_ctype")->lc_ctype,
            ],
            'encoding_info' => [
                'php_internal_encoding' => mb_internal_encoding(),
                'default_charset' => ini_get('default_charset'),
                'detected_encodings' => mb_detect_order(),
                'mbstring_encoding_translation' => ini_get('mbstring.encoding_translation'),
                'filesystem_encoding' => PHP_OS_FAMILY === 'Windows' ? 'UTF-16LE' : 'UTF-8'
            ]
        ]);
    }
}
