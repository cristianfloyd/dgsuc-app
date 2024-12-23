<?php

namespace App\Models\Reportes;

use Illuminate\Support\Facades\Log;
use App\Models\Mapuche\Catalogo\Dh30;
use App\Services\RepOrdenPagoService;
use App\Traits\MapucheConnectionTrait;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Model;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RepOrdenPagoModel extends Model implements HasLabel
{
    use MapucheConnectionTrait, HasFactory;

    const int TABLA_UNIDAD_ACADEMICA = 13;
    protected static ?string $label = 'Orden de Pago';
    protected $primaryKey = 'id';
    public $timestamps = true;
    public $incrementing = true;
    protected $table = 'suc.rep_orden_pago';


    protected $fillable = [
        'nro_liqui',
        'banco',
        'codn_funci',
        'codn_fuent',
        'codc_uacad',
        'caracter',
        'codn_progr',
        'remunerativo',
        'no_remunerativo',
        'descuentos',
        'aportes',
        'sueldo',
        'estipendio',
        'med_resid',
        'productividad',
        'sal_fam',
        'hs_extras',
        'total',
    ];

    /**
     * Indica los tipos de datos que deben ser convertidos automáticamente al acceder a los atributos del modelo.
     * @var array
     */
    protected $casts = [
        'nro_liqui' => 'integer',
        'banco' => 'integer',
        'remunerativo' => 'decimal:2',
        'no_remunerativo' => 'decimal:2',
        'descuentos' => 'decimal:2',
        'aportes' => 'decimal:2',
        'sueldo' => 'decimal:2',
        'estipendio' => 'decimal:2',
        'med_resid' => 'decimal:2',
        'productividad' => 'decimal:2',
        'sal_fam' => 'decimal:2',
        'hs_extras' => 'decimal:2',
        'total' => 'decimal:2',
    ];


    public static function generarReporte(array $nro_liqui): void
    {
        app(RepOrdenPagoService::class)->generateReport($nro_liqui);
    }


    public static function createTableIfNotExists(): bool
    {
        try {
            if (!Schema::connection('pgsql-mapuche')->hasTable('suc.rep_orden_pago')) {
                Schema::connection('pgsql-mapuche')->create('suc.rep_orden_pago', function (Blueprint $table) {
                    $table->id();
                    $table->integer('nro_liqui');
                    $table->string('desc_liqui');
                    $table->integer('banco');
                    $table->integer('codn_funci');
                    $table->integer('codn_fuent');
                    $table->string('codc_uacad');
                    $table->string('caracter');
                    $table->integer('codn_progr');
                    $table->string('desc_progr');
                    $table->decimal('remunerativo', 15, 2);
                    $table->decimal('no_remunerativo', 15, 2);
                    $table->decimal('descuentos', 15, 2);
                    $table->decimal('aportes', 15, 2);
                    $table->decimal('sueldo', 15, 2);
                    $table->decimal('estipendio', 15, 2);
                    $table->decimal('med_resid', 15, 2);
                    $table->decimal('productividad', 15, 2);
                    $table->decimal('sal_fam', 15, 2);
                    $table->decimal('hs_extras', 15, 2);
                    $table->decimal('total', 15, 2);
                    $table->string('periodo_fiscal', 6);
                    $table->timestamps();
                    $table->timestamp('last_sync')->useCurrent();

                    // Índices para optimizar consultas
                    $table->index(['periodo_fiscal', 'nro_liqui']);
                    $table->index('codc_uacad');
                    $table->index('banco');
                });

                Log::info("Tabla suc.rep_orden_pago creada exitosamente");
                return true;
            }
        } catch (\Exception $e) {
            Log::error("Error al crear tabla suc.rep_orden_pago: " . $e->getMessage());
            throw $e;
        }
        return false;
    }

    /* ####################################################################### */
    /* ########################## RELACIONES ################################# */
    public function unidadAcademica(): BelongsTo
    {
        return $this->belongsTo(Dh30::class, 'codc_uacad', 'desc_abrev')
            ->where('nro_tabla', self::TABLA_UNIDAD_ACADEMICA);
    }

    /* ####################################################################### */
    /* ################ SCOPES PARA BUSQUEDA Y FILTRO ######################## */
    public function scopeSearch(Builder $query, string $search): Builder
    {
        return $query->where(function ($query) use ($search) {
            $query->where('desc_liqui', 'like', "%{$search}%")
                  ->orWhere('codc_uacad', 'like', "%{$search}%")
                  ->orWhere('desc_progr', 'like', "%{$search}%");
        });
    }

    public function scopePeriodo($query, $periodo)
    {
        return $query->where('periodo_fiscal', $periodo);
    }

    public function scopePorLiquidacion($query, $nroLiqui)
    {
        return $query->where('nro_liqui', $nroLiqui);
    }

    public function scopeOrdenadoPorUacad($query)
    {
        return $query->orderBy('codc_uacad')->orderBy('nro_liqui');
    }

    public function getLabel(): string|null
    {
        return $this->label;
    }

    public function getPluralLabel(): string
    {
        return $this->label;
    }
}
