<?php

namespace App\Models;

use App\ValueObjects\NroLiqui;
use App\Enums\PuestoDesempenado;
use App\Traits\HasUnidadAcademica;
use Illuminate\Support\Facades\DB;
use App\ValueObjects\PeriodoFiscal;
use Illuminate\Support\Facades\Log;
use App\Traits\HasPuestoDesempenado;
use App\Traits\MapucheConnectionTrait;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Eloquent\Casts\Attribute;

class AfipMapucheMiSimplificacion extends Model
{
    use MapucheConnectionTrait, HasPuestoDesempenado, HasUnidadAcademica;

    protected $table = 'afip_mapuche_mi_simplificacion';
    protected $schema = 'suc';

    protected $primaryKey = 'id';
    public $incrementing = true;
    public $timestamps = false;

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




    protected static function boot()
    {
        parent::boot();

        static::retrieved(function ($model) {
            if ($model->attributes['actividad'] === null && $model->attributes['domicilio']) {
                $model->determinarCodigosUnidadAcademica($model->attributes['domicilio']);
            }

            // Determinar puesto si es necesario
            if ($model->attributes['puesto'] === null && $model->attributes['categoria']) {
                $puestoEnum = $model->determinarPuestoDesempenado($model->attributes['categoria']);
                if ($puestoEnum) {
                    // Actualizar el campo puesto en la base de datos
                    $model->puesto = $puestoEnum->value;
                    $model->save();
                }
            }
        });
    }

    // ############################ ACCESORS y MUTATORS ############################

    /**
     * Accessor para el periodo fiscal como objeto PeriodoFiscal
     */
    protected function periodoFiscalObject(): Attribute
    {
        return Attribute::make(
            get: function () {
                $periodoStr = $this->attributes['periodo_fiscal'] ?? null;
                if (!$periodoStr) {
                    return null;
                }

                try {
                    return PeriodoFiscal::fromString($periodoStr);
                } catch (\InvalidArgumentException $e) {
                    Log::warning("Formato de periodo fiscal inválido: {$periodoStr}");
                    return null;
                }
            }
        );
    }


    protected function puesto(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                // Inicializar la variable para evitar problemas de scope
                $puestoEnum = null;

                // Caso 1: Si tenemos un valor de puesto en la base de datos
                if ($value) {
                    // Primero intentamos determinar el puesto a partir del valor
                    $puestoEnum = $this->determinarPuestoDesempenado($value);

                    // Si no funciona, intentamos convertir directamente
                    if (!$puestoEnum) {
                        $puestoEnum = PuestoDesempenado::tryFrom($value);
                    }
                }
                // Caso 2: Si no tenemos puesto pero sí categoría, intentamos determinar el puesto
                elseif ($this->attributes['categoria']) {
                    $puestoEnum = $this->determinarPuestoDesempenado($this->attributes['categoria']);
                }

                return $puestoEnum;
            },
            set: fn ($value) => $value instanceof PuestoDesempenado ? $value->value : $value,
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

    /**
     * Accessor y Mutator para la fecha de inicio de relación laboral
     *
     * Reemplaza los guiones (-) por barras (/) en el formato de fecha
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    protected function inicioRelLaboral(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                return str_replace('-', '/', $value);
            },
            set: function ($value) {
                return str_replace('-', '/', $value);
            }
        );
    }

    /**
     * Accessor y Mutator para la fecha de fin de relación laboral
     *
     * Reemplaza los guiones (-) por barras (/) en el formato de fecha
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    protected function finRelLaboral(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                return str_replace('-', '/', $value);
            },
            set: function ($value) {
                return str_replace('-', '/', $value);
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
                $table->unique(['periodo_fiscal', 'cuil']);
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
        $instance = new static();
        DB::connection($instance->getConnectionName())->statement('TRUNCATE TABLE ' . $instance->getSchemaName() . '.afip_mapuche_mi_simplificacion RESTART identity CASCADE');
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
     * Scope para filtrar por periodo fiscal
     *
     * @param Builder $query
     * @param string|PeriodoFiscal $periodoFiscal
     * @return Builder
     */
    public function scopeByPeriodoFiscal($query, $periodoFiscal): Builder
    {
        $periodoStr = $periodoFiscal instanceof PeriodoFiscal
            ? $periodoFiscal->toString()
            : $periodoFiscal;

        return $query->where('periodo_fiscal', $periodoStr);
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

    // ##################### Funcion Almacencada #########################

    /** Inserta datos en la tabla suc.afip_mapuche_mi_simplificacion utilizando la función suc.get_mi_simplificacion_tt.
     *
     * @param int|NroLiqui $nroLiqui Número de liquidación.
     * @param int|string|PeriodoFiscal $periodoFiscal Período fiscal.
     * @return bool Verdadero si la inserción se realizó correctamente, falso en caso contrario.
     */
    public static function mapucheMiSimplificacion($nroLiqui, $periodoFiscal): bool
    {
        try {
            // Convertir a valor primitivo si es un objeto NroLiqui
            $nroLiquiValue = $nroLiqui instanceof NroLiqui ? $nroLiqui->value() : $nroLiqui;

            // Convertir a string si es un objeto PeriodoFiscal
            $periodoFiscalValue = $periodoFiscal;
            if ($periodoFiscal instanceof PeriodoFiscal) {
                $periodoFiscalValue = $periodoFiscal->toString();
            }

            // Obtener la conexión a la base de datos
            $connection = (new self())->getConnectionName();

            // Ejecutar la consulta de inserción
            DB::connection($connection)->statement(
                'INSERT INTO suc.afip_mapuche_mi_simplificacion
                SELECT * FROM suc.get_mi_simplificacion_tt(?, ?)',
                [$nroLiquiValue, $periodoFiscalValue]
            );
            Log::info('insert into suc.afip_mapuche_mi_simplificacion exitoso');

            return true;
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return false;
        }
    }
}
