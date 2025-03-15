<?php

namespace App\Models\Reportes;

use App\Services\EncodingService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Traits\MapucheConnectionTrait;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Eloquent\Casts\Attribute;

class ConceptoListado extends Model
{
    use MapucheConnectionTrait;
    private static $connectionInstance = null;

    protected $table = 'suc.rep_concepto_listado';
    protected $primaryKey = 'id';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'nro_liqui',
        'desc_liqui',
        'periodo_fiscal',
        'nro_legaj',
        'nro_cargo',
        'apellido',
        'nombre',
        'cuil',
        'codc_uacad',
        'codn_conce',
        'impp_conce'
    ];


    protected $appends = [
        'nombre_completo',
        'importe_formateado'
    ];

    public static function boot()
    {
        parent::boot();
        if(static::createTableIfNotExists()){
            Log::info("Tabla suc.rep_concepto_listado creada exitosamente");
        };
    }

    protected static function getMapucheConnection()
    {
        if (self::$connectionInstance === null) {
            $model = new static;
            self::$connectionInstance = $model->getConnectionFromTrait();
        }
        return self::$connectionInstance;
    }


    /**
     * Crea la tabla si no existe
     * @return bool
     */
    public static function createTableIfNotExists(): bool
    {
        $connection = static::getMapucheConnection();
        try {
            if (!Schema::connection($connection->getName())->hasTable('suc.rep_concepto_listado')) {
                Schema::connection($connection->getName())->create('suc.rep_concepto_listado', function (Blueprint $table) {
                    $table->id();
                    $table->string('nro_liqui')->nullable();
                    $table->string('desc_liqui')->nullable();
                    $table->string('periodo_fiscal')->nullable();
                    $table->integer('nro_legaj')->nullable();
                    $table->integer('nro_cargo')->nullable();
                    $table->string('apellido')->nullable();
                    $table->string('nombre')->nullable();
                    $table->string('cuil')->nullable();
                    $table->string('codc_uacad')->nullable();
                    $table->integer('codn_conce')->nullable();
                    $table->decimal('impp_conce', 15, 2)->nullable();

                    // Índices para optimizar consultas
                    $table->index(['periodo_fiscal', 'nro_liqui']);
                    $table->index('cuil');
                    $table->index('codn_conce');
                });

                Log::info("Tabla suc.rep_concepto_listado creada exitosamente");
                return true;
            }
        } catch (\Exception $e) {
            Log::error("Error al crear tabla suc.rep_concepto_listado: " . $e->getMessage());
            throw $e;
        }
        return false;
    }



    public function getNombreCompletoAttribute() {
        return "{$this->apellido}, {$this->nombre}";
    }

    public function getImporteFormateadoAttribute() {
        return number_format($this->impp_conce, 2, ',', '.');
    }



    // Scope para cachear resultados
    public function scopeCached($query)
    {
        $cacheKey = "rep_concepto_listado." . md5(request()->getQueryString());

        return Cache::tags(['rep_concepto_listado'])->remember(
            $cacheKey,
            now()->addHours(24),
            fn() => $query->get()
        );
    }

    public function scopePeriodo($query, $periodo) {
        return $query->where('periodo_fiscal', $periodo);
    }

    public function scopePorCuil($query, $cuil) {
        return $query->where('cuil', $cuil);
    }


    public function scopeWithLiquidacion(Builder $query, int $nroLiqui): Builder
    {
        return $query->where('nro_liqui', $nroLiqui);
    }

    // ######################## MUTADORES Y ACCESORES ########################
    public function apellido(): Attribute
    {
        return Attribute::make(
            get: fn($value) => EncodingService::toUtf8(strtoupper($value)),
            set: fn($value) => strtoupper($value),
        );
    }

    public function nombre(): Attribute
    {
        return Attribute::make(
            get: fn($value) => EncodingService::toUtf8(strtoupper($value)),
            set: fn($value) => strtoupper($value),
        );
    }
}
