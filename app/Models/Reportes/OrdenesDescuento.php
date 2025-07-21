<?php

namespace App\Models\Reportes;

use App\Services\EncodingService;
use Illuminate\Support\Facades\Log;
use App\Traits\MapucheConnectionTrait;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Model;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Schema\Blueprint;
use App\Services\OrdenesDescuentoTableService;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Contracts\Tables\OrdenesDescuentoTableDefinition;

class OrdenesDescuento extends Model implements HasLabel
{
    use HasFactory;
    use MapucheConnectionTrait;

    private static $connectionInstance = null;
    protected $primaryKey = 'id';
    public $incrementing = true;
    public $timestamps = true;
    protected $table = OrdenesDescuentoTableDefinition::TABLE;

    protected $fillable = [
        'nro_liqui',
        'desc_liqui',
        'codc_uacad',
        'desc_item',
        'codn_funci',
        'caracter',
        'tipoescalafon',
        'codn_fuent',
        'nro_inciso',
        'codn_progr',
        'codn_conce',
        'desc_conce',
        'impp_conce',
        'total_imp_nd',
        'last_sync'
    ];

    /**
     * Los atributos que deben ser convertidos a tipos nativos
     */
    protected $casts = [
        'fecha_emision' => 'datetime',
        'total_imp_def' => 'decimal:2',
        'total_imp_nd' => 'decimal:2',
    ];

    protected $encodedFields = ['desc_conce', 'desc_liqui', 'desc_item'];

    public static function boot()
    {
        parent::boot();

        $connection = static::getMapucheConnection();
        $connection->statement("SET client_encoding TO 'LATIN1'");

        static::retrieved(function ($model) {
            foreach ($model->encodedFields as $field) {
                if (isset($model->attributes[$field])) {
                    $model->attributes[$field] = iconv('ISO-8859-1', 'UTF-8', $model->attributes[$field]);
                }
            }
        });

        static::saving(function ($model) {
            foreach ($model->encodedFields as $field) {
                if (isset($model->attributes[$field])) {
                    $model->attributes[$field] = iconv('UTF-8', 'ISO-8859-1', $model->attributes[$field]);
                }
            }
        });
    }


    protected function sanitizeData(array $data): array
    {
        return array_map(function ($item) {
            if (is_string($item)) {
                return mb_convert_encoding($item, 'UTF-8', 'auto');
            }
            return $item;
        }, $data);
    }


    protected function handleEncodedField($value)
    {
        if (empty($value)) {
            return $value;
        }
        return iconv('UTF-8', 'UTF-8//TRANSLIT', $value);
    }

    protected function descItem(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $this->handleEncodedField($value),
            set: fn($value) => empty($value) ? $value : iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $value)
        );
    }


    public function getDataForSelect(): array
    {
        $data = $this->pluck('desc_conce', 'codn_conce')->toArray();
        return $this->sanitizeData($data);
    }

    protected static function getMapucheConnection()
    {
        if (self::$connectionInstance === null) {
            $model = new static();
            self::$connectionInstance = $model->getConnectionFromTrait();
        }
        return self::$connectionInstance;
    }

    protected static function initializeTable(): void
    {
        $tableService = new OrdenesDescuentoTableService(new OrdenesDescuentoTableDefinition());
        if (!$tableService->exists()) {
            $tableService->createAndPopulate();
        }
    }

    public static function createTableIfNotExists(): bool
    {
        try {
            $connection = (new static())->getConnectionName();

            if (!Schema::connection($connection)->hasTable('suc.rep_ordenes_descuento')) {
                Schema::connection($connection)->create('suc.rep_ordenes_descuento', function (Blueprint $table) {
                    $table->id();
                    $table->integer('nro_liqui');
                    $table->string('desc_liqui');
                    $table->string('codc_uacad');
                    $table->string('leyenda_sub_dependencia');
                    $table->integer('id_dependencia_de_pago');
                    $table->string('descripcion_dep_pago');
                    $table->integer('codn_funci');
                    $table->integer('id_beneficiario');
                    $table->string('descripcion_beneficiario');
                    $table->string('clase_liquidacion');
                    $table->integer('id_fuente_financiamiento');
                    $table->string('inciso');
                    $table->integer('id_programa');
                    $table->string('programa_descripcion');
                    $table->integer('id_programa_de_pago');
                    $table->string('descripcion_prog_pago');
                    $table->string('numero');
                    $table->date('fecha_emision');
                    $table->integer('anio');
                    $table->string('periodo_fiscal', 6);
                    $table->integer('id_tipo_orden_pago');
                    $table->integer('codn_conce');
                    $table->string('desc_conce');
                    $table->decimal('impp_conce', 15, 2);
                    $table->decimal('total_imp_nd', 15, 2);
                    $table->timestamp('last_sync')->useCurrent();

                    // Índices para optimizar consultas
                    $table->index(['periodo_fiscal', 'nro_liqui']);
                    $table->index('codc_uacad');
                    $table->index('codn_conce');
                });

                Log::info("Tabla suc.rep_ordenes_descuento creada exitosamente");
                return true;
            }
        } catch (\Exception $e) {
            Log::error("Error al crear tabla suc.rep_ordenes_descuento: " . $e->getMessage());
            throw $e;
        }
        return false;
    }

    /* ###################### Accesors y mutators ###################### */

    // protected function descItem(): Attribute
    // {
    //     return Attribute::make(
    //         get: function ($value) {
    //             if (empty($value)) return $value;

    //             // Si el valor viene como binary string (comienza con b")
    //             if (substr($value, 0, 2) === 'b"') {
    //                 // Remover b" del inicio y " del final
    //                 $value = substr($value, 2, -1);
    //             }

    //             // El valor hex muestra que está en ISO-8859-1
    //             // 0xed es el código para 'í' en ISO-8859-1
    //             $value = mb_convert_encoding($value, 'UTF-8', 'ISO-8859-1');

    //             // Verificar y limpiar caracteres no válidos
    //             return preg_replace('/[\x00-\x1F\x7F\xA0]/u', '', $value);
    //         },
    //         set: function ($value) {
    //             if (empty($value)) return $value;

    //             // Asegurar que el valor esté en UTF-8 antes de convertir
    //             if (!mb_check_encoding($value, 'UTF-8')) {
    //                 $value = mb_convert_encoding($value, 'UTF-8', 'ISO-8859-1');
    //             }

    //             // Convertir a ISO-8859-1 para almacenar
    //             return mb_convert_encoding($value, 'ISO-8859-1', 'UTF-8');
    //         }
    //     );
    // }

    protected function descLiqui(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $this->handleEncodedField($value),
            set: function ($value) {
                if (empty($value)) {
                    return $value;
                }
                return mb_convert_encoding($value, 'ISO-8859-1', 'UTF-8');
            }
        );
    }

    protected function descConce(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $this->handleEncodedField($value),
            set: function ($value) {
                if (empty($value)) {
                    return $value;
                }
                return mb_convert_encoding($value, 'ISO-8859-1', 'UTF-8');
            }
        );
    }

    // ######################  Scopes útiles para el reporte  ######################
    public function scopePeriodo($query, $periodo)
    {
        return $query->where('periodo_fiscal', $periodo);
    }

    public function scopePorLiquidacion($query, $nroLiqui)
    {
        return $query->where('nro_liqui', $nroLiqui);
    }

    // Método requerido por Filament para mostrar labels en forms y tablas
    public function getLabel(): ?string
    {
        return 'Orden de Descuento';
    }

    public static function getPluralLabel(): string
    {
        return 'Órdenes de Descuento';
    }

    // #######################  Scopes útiles para Filament  ######################
    // Scopes globales útiles para Filament
    public function scopeSearch(Builder $query, string $search): Builder
    {
        return $query->where(function ($query) use ($search) {
            $query->where('desc_liqui', 'like', "%{$search}%")
                ->orWhere('codc_uacad', 'like', "%{$search}%")
                ->orWhere('desc_conce', 'like', "%{$search}%");
        });
    }


    // Relaciones que pueden ser útiles en Filament
    public function scopeOrdenadoPorUacad($query)
    {
        return $query->orderBy('codc_uacad')->orderBy('codn_conce');
    }

    public function scopeWithLiquidacion(Builder $query, int $nroLiqui): Builder
    {
        return $query->where('nro_liqui', $nroLiqui);
    }

    // ############################# DIAGNOSTICOS #############################
    /**
     * Método de diagnóstico para problemas de codificación
     */
    public static function diagnosticarCodificacion($id)
    {
        try {
            $connection = static::getMapucheConnection();
            $connection->statement("SET client_encoding TO 'LATIN1'");

            $registro = static::find($id);

            if (!$registro) {
                return ['error' => 'Registro no encontrado'];
            }

            $campos = ['desc_conce', 'desc_liqui', 'desc_item'];
            $diagnostico = [];

            foreach ($campos as $campo) {
                if (isset($registro->$campo)) {
                    $valorOriginal = $registro->getRawOriginal($campo);

                    // Intentar diferentes conversiones
                    $valorConvertidoISO = mb_convert_encoding($valorOriginal, 'UTF-8', 'ISO-8859-1');
                    $valorConvertidoLATIN1 = mb_convert_encoding($valorOriginal, 'UTF-8', 'LATIN1');

                    $diagnostico[$campo] = [
                        'valor_original' => $valorOriginal,
                        'valor_raw_hex' => bin2hex($valorOriginal),
                        'valor_utf8' => mb_convert_encoding($valorOriginal, 'UTF-8', mb_detect_encoding($valorOriginal)),
                        'valor_desde_iso' => $valorConvertidoISO,
                        'valor_desde_latin1' => $valorConvertidoLATIN1,
                        'encoding_detectado' => mb_detect_encoding($valorOriginal, ['UTF-8', 'ISO-8859-1', 'ASCII'], true),
                        'longitud' => strlen($valorOriginal),
                        'longitud_mb' => mb_strlen($valorOriginal),
                        'caracteres_especiales' => preg_match('/[áéíóúÁÉÍÓÚñÑ]/', $valorOriginal) ? 'Sí' : 'No'
                    ];
                }
            }

            // Agregar información de codificación a nivel de base de datos
            $dbEncodings = $connection->select("
                SELECT
                    pg_encoding_to_char(encoding) as encoding,
                    datcollate,
                    datctype
                FROM pg_database
                WHERE datname = current_database()
            ");

            return [
                'id' => $id,
                'campos' => $diagnostico,
                'configuracion_db' => [
                    'client_encoding' => $connection->selectOne("SHOW client_encoding")->client_encoding,
                    'server_encoding' => $connection->selectOne("SHOW server_encoding")->server_encoding,
                    'database_encoding' => $dbEncodings[0]->encoding ?? 'Unknown',
                    'database_collate' => $dbEncodings[0]->datcollate ?? 'Unknown',
                    'database_ctype' => $dbEncodings[0]->datctype ?? 'Unknown',
                    'php_encoding' => mb_internal_encoding(),
                    'default_charset' => ini_get('default_charset')
                ]
            ];
        } catch (\Exception $e) {
            Log::error('Error en diagnóstico de codificación', [
                'id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return ['error' => $e->getMessage()];
        }
    }
}
