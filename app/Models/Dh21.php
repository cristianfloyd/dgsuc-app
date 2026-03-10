<?php

namespace App\Models;

use App\Contracts\Dh21RepositoryInterface;
use App\Models\Mapuche\Dh22;
use App\NroLiqui;
use App\Services\Mapuche\ConceptosTotalesService;
use App\Traits\MapucheConnectionTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Dh21 extends Model
{
    use MapucheConnectionTrait;

    public $timestamps = false;

    protected $table = 'dh21';

    protected $primaryKey = 'id_liquidacion';

    protected $fillable = [
        'nro_liqui',
        'nro_legaj',
        'nro_cargo',
        'codn_conce',
        'impp_conce',
        'tipo_conce',
        'nov1_conce',
        'nov2_conce',
        'nro_orimp',
        'tipoescalafon',
        'nrogrupoesc',
        'codigoescalafon',
        'codc_regio',
        'codc_uacad',
        'codn_area',
        'codn_subar',
        'codn_fuent',
        'codn_progr',
        'codn_subpr',
        'codn_proye',
        'codn_activ',
        'codn_obra',
        'codn_final',
        'codn_funci',
        'ano_retro',
        'mes_retro',
        'detallenovedad',
        'codn_grupo_presup',
        'tipo_ejercicio',
        'codn_subsubar',
    ];

    private Dh21RepositoryInterface $repository;

    public function __construct(?Dh21RepositoryInterface $repository = null)
    {
        if ($repository instanceof \App\Contracts\Dh21RepositoryInterface) {
            $this->repository = $repository;
        }

        parent::__construct();
    }

    /**
     * Relación de pertenencia con el modelo Dh22.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Mapuche\Dh22, $this>
     */
    public function dh22(): BelongsTo
    {
        return $this->belongsTo(Dh22::class, 'nro_liqui', 'nro_liqui');
    }

    public static function conceptosTotales(?NroLiqui $nro_liqui = null, ?int $codn_fuent = null): Builder
    {
        return resolve(ConceptosTotalesService::class)->calcular($nro_liqui, $codn_fuent);
    }

    /**
     * Obtiene la cantidad de legajos distintos en la tabla.
     */
    public static function distinctLegajos(): int
    {
        return resolve(Dh21RepositoryInterface::class)->getDistinctLegajos();
    }

    public static function distinctCargos()
    {
        return static::query()->distinct('nro_cargo')->count();
    }

    public static function distinctCodigoEscalafon(): array
    {
        return static::query()->pluck('codigoescalafon')->unique()->values()->toArray();
    }

    /**
     * Obtiene la suma total de todos los conceptos en la tabla.
     *
     * @return Collection
     */
    public static function totalConceptos()
    {
        return static::query()
            ->select('codn_conce', 'impp_conce')
            ->get()
            ->groupBy('codn_conce')
            ->orderBy('codn_conce')
            ->map(fn($group) => $group->sum('impp_conce'));
    }

    #[\Illuminate\Database\Eloquent\Attributes\Scope]
    protected function search($query, $search)
    {
        $columns = ['nro_legaj', 'nro_liqui', 'nro_cargo', 'codn_conce', 'impp_conce', 'tipo_conce', ];

        return $query->where(function ($q) use ($columns, $search): void {
            foreach ($columns as $column) {
                $q->orWhere($column, 'like', "%{$search}%");
            }
        });
    }

    #[\Illuminate\Database\Eloquent\Attributes\Scope]
    protected function conLiquidacionDefinitiva($query)
    {
        return $query->whereHas('dh22', function ($q): void {
            $q->definitiva();
        });
    }

    #[\Illuminate\Database\Eloquent\Attributes\Scope]
    protected function conFechaDeLiquidacion($query, $fecha)
    {
        return $query->whereHas('dh22', function ($q) use ($fecha): void {
            $q->where('fecha_liquidacion', $fecha);
        });
    }

    #[\Illuminate\Database\Eloquent\Attributes\Scope]
    protected function entreFechas($query, $fechaInicio, $fechaFin)
    {
        return $query->whereHas('dh22', function ($query) use ($fechaInicio, $fechaFin): void {
            $query->scopeBetweenPeriodoLiquidacion($fechaInicio, $fechaFin);
        });
    }

    /**
     * Obtiene una consulta de registros de Dh21 que tienen una liquidación definitiva asociada.
     *
     * @param Builder $query
     *
     * @return Builder
     */
    #[\Illuminate\Database\Eloquent\Attributes\Scope]
    protected function conDefinitiva($query)
    {
        return $query->whereHas('dh22', function ($q): void {
            $q->definitiva();
        });
    }

    /**
     * Obtiene la relación de liquidaciones asociadas a este registro.
     *
     * @return BelongsTo
     */
    private function getLiquidaciones()
    {
        return $this->belongsTo(Dh22::class, 'nro_liqui', 'nro_liqui');
    }
}
