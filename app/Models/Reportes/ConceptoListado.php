<?php

namespace App\Models\Reportes;

use Illuminate\Support\Facades\DB;
use App\Traits\MapucheConnectionTrait;
use Illuminate\Database\Eloquent\Model;
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

    public function newQuery()
    {
        $connection = $this->getConnectionName();
        // Obtenemos el concepto del filtro actual
        $conceptoFiltrado = request()->input('tableFilters.codn_conce');

        // Si no hay concepto seleccionado, retornamos una consulta vacía
        if (empty($conceptoFiltrado)) {
            // Creamos una subconsulta válida que siempre retorna vacío
            return parent::newQuery()
            ->from(DB::raw('(' . $this->getSqlQuery(999) . ') as concepto_listado'));
        }

        // Si hay concepto, construimos la consulta con el SQL
        return parent::newQuery()
            ->from(DB::raw('(' . $this->getSqlQuery($conceptoFiltrado) . ') as concepto_listado'));
    }

    private function getSqlQuery(array|int|null $concepto)
    {
        return "
            WITH legajo_cargo AS (
                SELECT DISTINCT ON (dh21.nro_legaj)
                    dh21.nro_legaj,
                    dh03.coddependesemp,
                    dh03.codc_uacad,
                    dh03.nro_cargo
                FROM mapuche.dh21
                INNER JOIN mapuche.dh03 ON dh21.nro_legaj = dh03.nro_legaj AND dh03.chkstopliq = 0
                when :codn_conce IS NULL THEN 999
                WHERE dh21.codn_conce = :codn_conce
            )
            SELECT
                --CONCAT(dh21.nro_legaj, '-', lc.coddependesemp, '-', dh21.nro_liqui, '-', dh21.codn_conce) AS id,
                dh21.nro_legaj,
                lc.codc_uacad,
                CONCAT(dh22.per_liano, LPAD(CAST(dh22.per_limes AS TEXT), 2, '0')) AS periodo_fiscal,
                --dh22.nro_liqui,
                dh22.desc_liqui,
                CONCAT(dh01.nro_cuil1, dh01.nro_cuil, dh01.nro_cuil2) AS cuil,
                lc.coddependesemp,
                lc.nro_cargo AS secuencia,
                dh21.codn_conce,
                --dh21.tipo_conce,
                dh21.impp_conce
            FROM mapuche.dh21
            INNER JOIN legajo_cargo lc ON dh21.nro_legaj = lc.nro_legaj
            INNER JOIN mapuche.dh01 ON dh21.nro_legaj = dh01.nro_legaj
            INNER JOIN mapuche.dh22 ON dh21.nro_liqui = dh22.nro_liqui
            when :codn_conce IS NULL THEN 999
            WHERE dh21.codn_conce = :codn_conce;
        ";
    }


    public function scopeWithLiquidacion(Builder $query, int $nroLiqui): Builder
    {
        return $query->where('dh21.nro_liqui', $nroLiqui);
    }
}
