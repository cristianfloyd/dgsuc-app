<?php

namespace App\Models\Reportes;

use Illuminate\Support\Facades\Log;
use App\Traits\MapucheConnectionTrait;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Model;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Schema\Blueprint;
use App\Services\OrdenesDescuentoTableService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Contracts\Tables\OrdenesDescuentoTableDefinition;

class OrdenesDescuento extends Model implements HasLabel
{
    use HasFactory, MapucheConnectionTrait;

    protected $primaryKey = 'id';
    public $incrementing = true;
    public $timestamps = true;
    protected $table = OrdenesDescuentoTableDefinition::TABLE_NAME;

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

    public static function boot()
    {
        parent::boot();

        if (static::createTableIfNotExists()) {
            Log::info("Tabla suc.rep_ordenes_descuento creada exitosamente");
        }
    }

    protected static function initializeTable(): void
    {
        $tableService = new OrdenesDescuentoTableService();
        if (!$tableService->exists()) {
            $tableService->createAndPopulate();
        }
    }

    public static function createTableIfNotExists(): bool
    {
        try {
            if (!Schema::connection('pgsql-mapuche')->hasTable('suc.rep_ordenes_descuento')) {
                Schema::connection('pgsql-mapuche')->create('suc.rep_ordenes_descuento', function (Blueprint $table) {
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
}
