<?php

namespace App\Models;

use App\Enums\PuestoDesempenado;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Traits\HasPuestoDesempenado;
use App\Traits\MapucheConnectionTrait;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Eloquent\Casts\Attribute;
use App\Traits\HasUnidadAcademica;

class AfipMapucheMiSimplificacion extends Model
{
    use MapucheConnectionTrait, HasPuestoDesempenado, HasUnidadAcademica;

    protected $table = 'afip_mapuche_mi_simplificacion';
    protected $schema = 'suc';

    protected $primaryKey = ['periodo_fiscal', 'cuil'];
    public $incrementing = false;

    // Campos que se pueden asignar masivament
    protected $fillable = [
        'id',
        'nro_legaj',
        'nro_liqui',
        'sino_cerra',
        'desc_estado_liquidacion',
        'nro_cargo',
        'periodo_fiscal',
        'tipo_registro',
        'codigo_movimiento',
        'cuil',
        'trabajador_agropecuario',
        'modalidad_contrato',
        'inicio_rel_laboral',
        'fin_rel_laboral',
        'obra_social',
        'codigo_situacion_baja',
        'fecha_tel_renuncia',
        'retribucion_pactada',
        'modalidad_liquidacion',
        'domicilio',
        'actividad',
        'puesto',
        'rectificacion',
        'ccct',
        'tipo_servicio',
        'categoria',
        'fecha_susp_serv_temp',
        'nro_form_agro',
        'covid'
    ];

    protected $appends = ['puesto_descripcion', 'puesto_escalafon'];

    protected $casts = [
        'puesto' => PuestoDesempenado::class,
    ];


    protected static function boot()
    {
        parent::boot();

        static::retrieved(function ($model)
        {
            if ($model->attributes['actividad'] === null && $model->attributes['domicilio']) {
                $model->determinarCodigosUnidadAcademica($model->attributes['domicilio']);
            }
        });
    }

    // ############################ ACCESORS ############################
    /**
     * Accessor y Mutator para el puesto
     */
    protected function puesto(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                if (!$value) {
                    return null;
                }

                $puestoEnum = $this->determinarPuestoDesempenado($value);

                if (!$puestoEnum) {
                    $puestoEnum = PuestoDesempenado::tryFrom($value);
                }

                return $puestoEnum;
            }
        );
    }

    /**
     * Accessor para la descripción del puesto
     */
    protected function puestoDescripcion(): Attribute
    {
        return Attribute::make(
            get: function () {
                $categoria = $this->attributes['puesto'] ?? null;

                if (!$categoria) {
                    return null;
                }

                $puesto = $this->determinarPuestoDesempenado($categoria);
                return $puesto?->descripcion();
            }
        );
    }

    /**
     * Accessor para el escalafón del puesto
     */
    protected function puestoEscalafon(): Attribute
    {
        return Attribute::make(
            get: function () {
                $categoria = $this->attributes['puesto'] ?? null;

                if (!$categoria) {
                    return null;
                }

                $puesto = $this->determinarPuestoDesempenado($categoria);
                return $puesto?->escalafon();
            }
        );
    }

    /**
     * Accessor y Mutator para el domicilio
     */
    protected function domicilio(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                // Si tenemos actividad null pero un código de unidad válido,
                // intentamos determinar los códigos
                if ($this->attributes['actividad'] === null && $value) {
                    $this->determinarCodigosUnidadAcademica($value);
                }
                return str_pad($value, 5, '0', STR_PAD_LEFT);
            },
            set: function ($value) {
                $this->determinarCodigosUnidadAcademica($value);
                return $value;
            }
        );
    }


    // ################################################################

    // ################################################################

    public function getSchema(): string
    {
        return $this->schema;
    }

    public function getTableName(): string
    {
        return $this->table;
    }

    public function getFullTableName(): string
    {
        return "{$this->schema}.{$this->table}";
    }

    /**
     * Obtiene el valor de la clave primaria.
     *
     * @return string
     */
    public function getKey(): string
    {
        return "{$this->periodo_fiscal}-{$this->cuil}";
    }

    /**
     * Obtiene la clave primaria del modelo.
     *
     * @return string
     */
    public function getKeyName(): string
    {
        return 'id';
    }

    /**
     * Determina si la clave primaria es compuesta.
     *
     * @return bool
     */
    public function getIncrementing()
    {
        return $this->incrementing;
    }

    public function getRouteKey(): string
    {
        return "{$this->periodo_fiscal}|{$this->cuil}";
    }

    public function getRouteKeyName(): string
    {
        return 'id';
    }

    /**
     * Recupera el modelo por su clave única.
     *
     * @param  mixed  $key
     * @param  string|null  $field
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Collection|static[]|static|null
     */
    public function resolveRouteBinding($key, $field = null)
    {
        if ($field === 'id') {
            [$periodo_fiscal, $cuil] = explode('-', $key);
            return $this->where('periodo_fiscal', $periodo_fiscal)
                ->where('cuil', $cuil)
                ->first();
        }
        return parent::resolveRouteBinding($key, $field);
    }

    /**
     * Recupera un modelo por su clave única compuesta.
     *
     * @param string $id La clave única compuesta en el formato "periodo_fiscal-cuil".
     * @param array $columns Los campos a recuperar (por defecto, todos los campos).
     * @return \Illuminate\Database\Eloquent\Model|null El modelo encontrado, o null si no se encuentra.
     */
    public function find($id, $columns = ['*'])
    {
        list($periodo_fiscal, $cuil) = explode('-', $id);
        return $this->where('periodo_fiscal', $periodo_fiscal)
            ->where('cuil', $cuil)
            ->first($columns);
    }

    /**
     * Obtiene una nueva instancia de query para el modelo.
     *
     * @return Builder
     */
    public function newQuery()
    {
        return parent::newQuery()
            ->from("{$this->getFullTableName()} as ami")
            ->addSelect(
                'ami.*',
                DB::connection($this->getConnectionName())->raw("CONCAT(periodo_fiscal, '-', cuil) as id")
            );
    }

    // Método para consultas grandes
    public function scopeChunked($query, $callback, $count = 1000)
    {
        $query->chunk($count, $callback);
    }

    public function createTable(): bool
    {
        if (!Schema::connection($this->getConnectionName())->hasTable($this->table)) {
            Schema::connection($this->getConnectionName())->create($this->table, function (Blueprint $table) {
                $table->id();
                $table->integer('nro_legaj');
                $table->char('nro_liqui', 6);
                $table->char('sino_cerra', 1);
                $table->string('desc_estado_liquidacion', 50);
                $table->integer('nro_cargo');
                $table->char('periodo_fiscal', 6);
                $table->char('tipo_registro', 2)->default('01');
                $table->char('codigo_movimiento', 2)->default('AT');
                $table->char('cuil', 11);
                $table->char('trabajador_agropecuario', 1)->default('N');
                $table->char('modalidad_contrato', 3)->default('008')->nullable();
                $table->char('inicio_rel_laboral', 10);
                $table->char('fin_rel_laboral', 10)->nullable();
                $table->char('obra_social', 6)->default('000000')->nullable();
                $table->char('codigo_situacion_baja', 2)->nullable();
                $table->char('fecha_tel_renuncia', 10)->nullable();
                $table->char('retribucion_pactada', 15)->nullable();
                $table->char('modalidad_liquidacion', 1)->default('1');
                $table->char('domicilio', 5)->nullable();
                $table->char('actividad', 6)->nullable();
                $table->char('puesto', 4)->nullable();
                $table->char('rectificacion', 2)->nullable();
                $table->char('ccct', 10)->nullable()->default('0000000000');
                $table->char('tipo_servicio', 3)->nullable();
                $table->char('categoria', 6)->nullable();
                $table->char('fecha_susp_serv_temp', 10)->nullable();
                $table->char('nro_form_agro', 10)->nullable();
                $table->char('covid', 1)->nullable();

                // Definición de la clave primaria compuesta
                $table->primary(['periodo_fiscal', 'cuil']);
            });
            Log::info("Tabla {$this->table} creada en la base de datos {$this->connection}, desde el modelo");
            return true; // Table created successfully
        }
        Log::info("Tabla {$this->table} ya existe en la base de datos {$this->connection}, desde el modelo");
        return false; // Table already exists
    }

    /**
     * Trunca la tabla y reinicia las identidades.
     */
    public static function truncate()
    {

        DB::connection('pgsql-prod')->statement('TRUNCATE TABLE suc.afip_mapuche_mi_simplificacion RESTART identity CASCADE');
    }

    /**
     * Retorna las columnas de la tabla.
     *
     * @return array
     */
    public function getTableHeaders()
    {
        return $this->fillable;
    }

    /**
     * Retorna el nombre de la tabla en la base de datos.
     *
     * @return string
     */
    static function getDatabaseTableName()
    {
        return static::getTable();
    }

    /**
     * Scope para búsqueda por CUIL o número de legajo.
     *
     * @param Builder $query
     * @param string $value
     * @return Builder
     */
    public function scopeSearch($query, $value)
    {
        return empty($value) ? $query :  $query->where('cuil', 'ilike', "%$value%")
            ->orWhere('nro_legaj', 'ilike', "%$value%");
    }

    public function getSchemaName(): string
    {
        return $this->schema;
    }





    /**
     * Scope para filtrar por tipo de puesto
     */
    public function scopeByPuesto($query, PuestoDesempenado $puesto)
    {
        $categorias = match ($puesto) {
            PuestoDesempenado::PROFESOR_UNIVERSITARIO => $this->getCategoriesByGroup('DOCU'),
            PuestoDesempenado::PROFESOR_SECUNDARIO => array_merge(
                $this->getCategoriesByGroup('DOCS'),
                $this->getCategoriesByGroup('DOC2')
            ),
            PuestoDesempenado::NODOCENTE => $this->getCategoriesByGroup('NODO'),
            PuestoDesempenado::DIRECTIVO => array_merge(
                $this->getCategoriesByGroup('AUTU'),
                $this->getCategoriesByGroup('AUTS')
            ),
            default => [],
        };

        // Asegurarnos de que las categorías se pasen como strings
        return $query->whereIn('puesto', array_map('strval', $categorias));
    }

    /**
     * Método helper para debug de categorías
     */
    public static function debugCategorias(PuestoDesempenado $puesto): array
    {
        $model = new static;
        $categorias = match ($puesto) {
            PuestoDesempenado::PROFESOR_UNIVERSITARIO => $model->getCategoriesByGroup('DOCU'),
            PuestoDesempenado::PROFESOR_SECUNDARIO => array_merge(
                $model->getCategoriesByGroup('DOCS'),
                $model->getCategoriesByGroup('DOC2')
            ),
            PuestoDesempenado::NODOCENTE => $model->getCategoriesByGroup('NODO'),
            PuestoDesempenado::DIRECTIVO => array_merge(
                $model->getCategoriesByGroup('AUTU'),
                $model->getCategoriesByGroup('AUTS')
            ),
            default => [],
        };

        return array_map('strval', $categorias);
    }

    /**
     * Método helper para debug de categorías y consultas
     */
    public static function debugPuestoQuery(PuestoDesempenado $puesto): array
    {
        $query = static::byPuesto($puesto);

        return [
            'sql' => $query->toSql(),
            'bindings' => $query->getBindings(),
            'categorias_usadas' => static::debugCategorias($puesto),
        ];
    }
}
