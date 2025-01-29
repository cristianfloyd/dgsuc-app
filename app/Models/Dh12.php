<?php

namespace App\Models;

use App\Models\Dh13;
use App\Enums\TipoNove;
use App\Enums\TipoConce;
use App\Enums\TipoDistr;
use App\Enums\ConceptoGrupo;
use App\Services\EncodingService;
use Illuminate\Support\Facades\DB;
use App\Traits\CharacterEncodingTrait;
use App\Traits\MapucheConnectionTrait;
use Illuminate\Database\Eloquent\Model;
use App\Traits\MapucheLiquiConnectionTrait;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Dh12 extends Model
{
    use MapucheConnectionTrait;
    use CharacterEncodingTrait;

    private static $connectionInstance = null;

    /**
     * La tabla asociada con el modelo.
     *
     * @var string
     */
    protected $table = 'dh12';

    /**
     * La clave primaria asociada con la tabla.
     *
     * @var string
     */
    protected $primaryKey = 'codn_conce';

    /**
     * Indica si el modelo debe ser timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Los atributos que son asignables en masa.
     *
     * @var array
     */
    protected $fillable = [
        'codn_conce',
        'vig_coano', 'vig_comes', 'desc_conce', 'desc_corta', 'tipo_conce',
        'codc_vige1', 'desc_nove1', 'tipo_nove1', 'cant_ente1', 'cant_deci1',
        'codc_vige2', 'desc_nove2', 'tipo_nove2', 'cant_ente2', 'cant_deci2',
        'flag_acumu', 'flag_grupo', 'nro_orcal', 'nro_orimp', 'sino_legaj',
        'tipo_distr', 'tipo_ganan', 'chk_acumsac', 'chk_acumproy', 'chk_dcto3',
        'chkacumprhbrprom', 'subcicloliquida', 'chkdifhbrcargoasoc',
        'chkptesubconcep', 'chkinfcuotasnovper', 'genconimp0', 'sino_visible'
    ];

    /**
     * Los atributos que deben ser convertidos a tipos nativos.
     *
     * @var array
     */
    protected $casts = [
        'tipo_conce' => TipoConce::class,
        'tipo_nove1' => TipoNove::class,
        'tipo_nove2' => TipoNove::class,
        'tipo_distr' => TipoDistr::class,
        'chk_acumsac' => 'boolean',
        'chk_acumproy' => 'boolean',
        'chk_dcto3' => 'boolean',
        'chkacumprhbrprom' => 'boolean',
        'chkdifhbrcargoasoc' => 'boolean',
        'chkptesubconcep' => 'boolean',
        'chkinfcuotasnovper' => 'boolean',
        'genconimp0' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();
        $connection = static::getMapucheConnection();

        // Configuramos la sesión para manejar correctamente los caracteres
        $connection->statement("SET client_encoding TO 'SQL_ASCII'");

        static::retrieved(function ($model) {
            // Al recuperar datos, convertimos de ISO-8859-1 a UTF-8
            if (isset($model->attributes['desc_conce'])) {
                $model->attributes['desc_conce'] = EncodingService::toLatin1($model->attributes['desc_conce']);
            }
        });

        static::saving(function ($model) {
            // Al guardar datos, convertimos de UTF-8 a ISO-8859-1
            if (isset($model->attributes['desc_conce'])) {
                $model->attributes['desc_conce'] = mb_convert_encoding(
                    $model->attributes['desc_conce'],
                    'ISO-8859-1',
                    'UTF-8'
                );
            }
        });
    }

    protected static function getMapucheConnection()
    {
        if (self::$connectionInstance === null) {
            $model = new static;
            self::$connectionInstance = $model->getConnectionFromTrait();
        }
        return self::$connectionInstance;
    }

    public function perteneceAGrupo(ConceptoGrupo $grupo): bool
    {
        return $grupo->containsConcepto($this->codn_conce);
    }


    // ######################## RELACIONES ########################
    /**
     * Obtiene los Dh13 asociados con este Dh12.
     */
    public function dh13s(): HasMany
    {
        return $this->hasMany(Dh13::class, 'codn_conce', 'codn_conce');
    }


    // ######################## ACCESORES ########################
    /**
     * Accessor para asegurar la codificación UTF-8 del campo desc_conce
     */
    protected function descConce(): Attribute
    {

        return Attribute::make(
            get: fn ($value) => EncodingService::toUtf8($value),
            set: fn ($value) => EncodingService::toLatin1($value)
        );
    }

    /**
     * Método para obtener el texto formateado para el select
     */
    public function getSelectLabelAttribute(): string
    {
        try {
            return sprintf(
                '%d - %s',
                $this->codn_conce,
                EncodingService::toUtf8($this->desc_conce)
            );
        } catch (\Exception $e) {
            return sprintf('%d - %s', $this->codn_conce, $this->cleanAndEncodeString($this->desc_conce));
        }
    }


    // ############################# DIAGNOSTICOS #############################
    /**
     * Método de diagnóstico mejorado
     */
    /**
     * Diagnóstico mejorado para caracteres especiales
     */

    public static function diagnosticarCodificacion($codn_conce)
    {
        // Usamos la conexión específica
        $connection = static::getMapucheConnection();

        $connection->statement("SET client_encoding TO 'SQL_ASCII'");

        $resultado = $connection->select("
            SELECT
                codn_conce,
                desc_conce,
                encode(desc_conce::bytea, 'hex') as bytes_hex,
                length(desc_conce) as longitud_texto,
                current_setting('server_encoding') as server_encoding
            FROM mapuche.dh12
            WHERE codn_conce = ?
        ", [$codn_conce]);

        if (empty($resultado)) {
            return ['error' => 'No se encontró el registro'];
        }

        $registro = $resultado[0];

        // Análisis byte por byte
        $bytes = str_split($registro->desc_conce);
        $analisis_bytes = array_map(function($byte) {
            return [
                'byte' => bin2hex($byte),
                'ascii' => ord($byte)
            ];
        }, $bytes);

        return [
            'codn_conce' => $registro->codn_conce,
            'valor_crudo' => $registro->desc_conce,
            'valor_utf8' => EncodingService::toUtf8($registro->desc_conce),
            'valor_latin1' => EncodingService::toLatin1($registro->desc_conce),
            'longitud' => $registro->longitud_texto,
            'bytes_hex' => $registro->bytes_hex,
            'analisis_bytes' => $analisis_bytes,
            'configuracion_db' => [
                'client_encoding' => $connection->selectOne("SHOW client_encoding")->client_encoding,
                'server_encoding' => $registro->server_encoding,
                'php_encoding' => mb_internal_encoding()
            ]
        ];
    }
}
