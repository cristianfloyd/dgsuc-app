<?php

declare(strict_types=1);

namespace App\Models\Reportes;

use App\Models\Mapuche\Catalogo\Dh30;
use App\Services\RepOrdenPagoService;
use App\Traits\MapucheConnectionTrait;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RepOrdenPagoModel extends Model implements HasLabel
{
    use MapucheConnectionTrait;
    use HasFactory;

    public const TABLA_UNIDAD_ACADEMICA = 13;

    public $timestamps = true;

    public $incrementing = true;

    protected static ?string $label = 'Orden de Pago';

    protected $primaryKey = 'id';

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
        'otros_no_remunerativo', // Nuevo campo
        'bruto',                 // Nuevo campo
        'descuentos',
        'aportes',
        'sueldo',
        'neto',                 // Nuevo campo
        'estipendio',
        'med_resid',
        'productividad',
        'sal_fam',
        'hs_extras',
        'total',
        'imp_gasto',
    ];

    /**
     * Indica los tipos de datos que deben ser convertidos automáticamente al acceder a los atributos del modelo.
     *
     * @var array
     */
    protected $casts = [
        'nro_liqui' => 'integer',
        'banco' => 'integer',
        'remunerativo' => 'decimal:2',
        'no_remunerativo' => 'decimal:2',
        'otros_no_remunerativo' => 'decimal:2',
        'bruto' => 'decimal:2',
        'descuentos' => 'decimal:2',
        'aportes' => 'decimal:2',
        'sueldo' => 'decimal:2',
        'neto' => 'decimal:2',
        'estipendio' => 'decimal:2',
        'med_resid' => 'decimal:2',
        'productividad' => 'decimal:2',
        'sal_fam' => 'decimal:2',
        'hs_extras' => 'decimal:2',
        'total' => 'decimal:2',
        'imp_gasto' => 'decimal:2',
    ];

    public static function generarReporte(array $nro_liqui): void
    {
        app(RepOrdenPagoService::class)->generateReport($nro_liqui);
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
        return $query->where(function ($query) use ($search): void {
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

    protected static function boot(): void
    {
        parent::boot();

        static::saving(function ($model): void {
            // Validacion de campos no negativos
            $camposMonetarios = [
                'remunerativo',
                'no_remunerativo',
                'otros_no_remunerativo',
                'bruto',
                'descuentos',
                'aportes',
                'sueldo',
                'neto',
                'estipendio',
                'med_resid',
                'productividad',
                'sal_fam',
                'hs_extras',
                'total',
                'imp_gasto',
            ];

            foreach ($camposMonetarios as $campo) {
                if ($model->$campo < 0) {
                    throw new \InvalidArgumentException("El campo {$campo} no puede ser negativo");
                }
            }
        });
    }
}
