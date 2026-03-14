<?php

namespace App\Models\Reportes;

use App\Services\EncodingService;
use App\Traits\MapucheConnectionTrait;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Override;

class ConceptoListado extends Model
{
    use MapucheConnectionTrait;

    public $incrementing = false;

    public $timestamps = false;

    protected $table = 'suc.rep_concepto_listado';

    protected $primaryKey = 'id';

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
        'impp_conce',
    ];

    protected $appends = [
        'nombre_completo',
        'importe_formateado',
    ];

    private static $connectionInstance;

    #[Override]
    public static function boot(): void
    {
        parent::boot();
        if (static::createTableIfNotExists()) {
            Log::info('Tabla suc.rep_concepto_listado creada exitosamente');
        }
    }

    /**
     * Crea la tabla si no existe.
     */
    public static function createTableIfNotExists(): bool
    {
        $connection = static::getMapucheConnection();
        try {
            if (!Schema::connection($connection->getName())->hasTable('suc.rep_concepto_listado')) {
                Schema::connection($connection->getName())->create('suc.rep_concepto_listado', function (Blueprint $table): void {
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

                Log::info('Tabla suc.rep_concepto_listado creada exitosamente');

                return true;
            }
        } catch (Exception $e) {
            Log::error('Error al crear tabla suc.rep_concepto_listado: ' . $e->getMessage());
            throw $e;
        }

        return false;
    }

    public static function getMapucheConnection()
    {
        if (self::$connectionInstance === null) {
            $model = new self();
            self::$connectionInstance = $model->getConnectionFromTrait();
        }

        return self::$connectionInstance;
    }

    protected function nombreCompleto(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return \Illuminate\Database\Eloquent\Casts\Attribute::make(get: fn(): string => "{$this->apellido}, {$this->nombre}");
    }

    protected function importeFormateado(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return \Illuminate\Database\Eloquent\Casts\Attribute::make(get: fn(): string => number_format($this->impp_conce, 2, ',', '.'));
    }

    // Scope para cachear resultados
    #[\Illuminate\Database\Eloquent\Attributes\Scope]
    protected function cached($query)
    {
        $cacheKey = 'rep_concepto_listado.' . md5((string) request()->getQueryString());

        return Cache::tags(['rep_concepto_listado'])->remember(
            $cacheKey,
            now()->addHours(24),
            fn() => $query->get(),
        );
    }

    #[\Illuminate\Database\Eloquent\Attributes\Scope]
    protected function periodo($query, $periodo)
    {
        return $query->where('periodo_fiscal', $periodo);
    }

    #[\Illuminate\Database\Eloquent\Attributes\Scope]
    protected function porCuil($query, $cuil)
    {
        return $query->where('cuil', $cuil);
    }

    #[\Illuminate\Database\Eloquent\Attributes\Scope]
    protected function withLiquidacion(Builder $query, int $nroLiqui): Builder
    {
        return $query->where('nro_liqui', $nroLiqui);
    }

    // ######################## MUTADORES Y ACCESORES ########################
    protected function apellido(): Attribute
    {
        return Attribute::make(
            get: fn($value): ?string => EncodingService::toUtf8(strtoupper((string) $value)),
            set: fn($value) => strtoupper((string) $value),
        );
    }

    protected function nombre(): Attribute
    {
        return Attribute::make(
            get: fn($value): ?string => EncodingService::toUtf8(strtoupper((string) $value)),
            set: fn($value) => strtoupper((string) $value),
        );
    }
}
