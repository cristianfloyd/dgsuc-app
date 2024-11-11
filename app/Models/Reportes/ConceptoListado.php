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


    protected $table = 'suc.concepto_listado';
    protected $primaryKey = 'id';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'nro_legaj',
        'codc_uacad',
        'periodo_fiscal',
        'nro_liqui',
        'desc_liqui',
        'apellido',
        'nombre',
        'cuil',
        'secuencia',
        'codn_conce',
        'impp_conce'
    ];


    public function getNombreCompletoAttribute() {
        return "{$this->apellido}, {$this->nombre}";
    }

    public function getImporteFormateadoAttribute() {
        return number_format($this->impp_conce, 2, ',', '.');
    }



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
