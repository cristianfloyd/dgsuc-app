<?php

namespace App\Models;

use App\NroLiqui;
use App\Models\Mapuche\Dh22;
use App\Traits\MapucheConnectionTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use App\Contracts\Dh21RepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use App\Services\Mapuche\ConceptosTotalesService;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Dh21 extends Model
{
    use MapucheConnectionTrait;

    private Dh21RepositoryInterface  $repository;
    protected $table = 'dh21';
    public $timestamps = false;
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
        'codn_subsubar'
    ];

    public function __construct(Dh21RepositoryInterface $repository = null)
    {
        if($repository){
            $this->repository = $repository;
        }

        parent::__construct();
    }

    /**
     * Relación de pertenencia con el modelo Dh22.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function dh22(): BelongsTo
    {
        return $this->belongsTo(Dh22::class, 'nro_liqui', 'nro_liqui');
    }

    public function scopeSearch($query, $search)
    {
        $columns = ['nro_legaj', 'nro_liqui', 'nro_cargo', 'codn_conce', 'impp_conce', 'tipo_conce',];

        return $query->where(function ($q) use ($columns, $search) {
            foreach ($columns as $column) {
                $q->orWhere($column, 'like', "%{$search}%");
            }
        });
    }

    public static function conceptosTotales(NroLiqui $nro_liqui = null, int $codn_fuent = null): Builder
    {
        return app(ConceptosTotalesService::class)->calcular($nro_liqui, $codn_fuent);
    }

    /**
     * Obtiene la cantidad de legajos distintos en la tabla.
     *
     * @return int
     */
    public static function distinctLegajos()
    {
        return app(Dh21RepositoryInterface::class)->getDistinctLegajos();
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
            ->map(function ($group) {
                return $group->sum('impp_conce');
            });
    }

    /**
     * Obtiene la relación de liquidaciones asociadas a este registro.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    private function getLiquidaciones()
    {
        return $this->belongsTo(Dh22::class, 'nro_liqui', 'nro_liqui');
    }

}
