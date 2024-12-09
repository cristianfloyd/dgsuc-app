<?php

namespace App\Models\Reportes;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Traits\MapucheConnectionTrait;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Model;
use App\Services\ConceptoListadoService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Schema\Blueprint;

class ConceptoListado extends Model
{
    use MapucheConnectionTrait;


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

    /**
     * Crea la tabla si no existe
     * @return bool
     */
    public static function createTableIfNotExists(): bool
    {
        try {
            if (!Schema::connection('pgsql-mapuche')->hasTable('suc.rep_concepto_listado')) {
                Schema::connection('pgsql-mapuche')->create('suc.rep_concepto_listado', function (Blueprint $table) {
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

    /**
     * Trunca la tabla para limpiar datos temporales
     */
    public static function truncateTable(): void
    {
        try {
            DB::connection('pgsql-mapuche')
                ->statement('TRUNCATE TABLE suc.rep_concepto_listado RESTART IDENTITY CASCADE');
            Cache::tags(['rep_concepto_listado'])->flush();
        } catch (\Exception $e) {
            Log::error("Error al truncar tabla suc.rep_concepto_listado: " . $e->getMessage());
            throw $e;
        }
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
}
