<?php

namespace App\Models\Reportes;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Traits\MapucheConnectionTrait;
use Illuminate\Database\Eloquent\Model;
use App\Services\ConceptoListadoService;
use Illuminate\Database\Eloquent\Builder;

class ConceptoListado extends Model
{
    use MapucheConnectionTrait;


    protected $table = 'concepto_listado';
    protected $primaryKey = 'id';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'codc_uacad',
        'periodo_fiscal',
        'nro_liqui',
        'desc_liqui',
        'nro_legaj',
        'cuil',
        'apellido',
        'nombre',
        'oficina_pago',
        'codc_categ',
        'codigoescalafon',
        'secuencia',
        'categoria_completa',
        'codn_conce',
        'tipo_conce',
        'impp_conce'
    ];

    // Scope para cachear resultados
    public function scopeCached($query)
    {
        $cacheKey = "concepto_listado." . md5(request()->getQueryString());

        return Cache::tags(['concepto_listado'])->remember(
            $cacheKey,
            now()->addHours(24),
            fn() => $query->get()
        );
    }






    public function scopeWithLiquidacion(Builder $query, int $nroLiqui): Builder
    {
        return $query->where('nro_liqui', $nroLiqui);
    }
}
