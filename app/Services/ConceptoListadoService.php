<?php

namespace App\Services;

use App\Models\Dh01;
use Illuminate\Support\Facades\DB;
use App\Traits\MapucheConnectionTrait;
use App\Models\Reportes\ConceptoListado;
use Illuminate\Database\Eloquent\Builder;

class ConceptoListadoService
{
    use MapucheConnectionTrait;

    /**
     * Obtiene una consulta de Eloquent para el concepto especificado.
     * Asegura un registro único por legajo considerando su cargo activo.
     *
     * @param int|array $codn_conce El código del concepto a buscar.
     * @return \Illuminate\Database\Eloquent\Builder La consulta de Eloquent.
     */
    public function getQueryForConcepto(int|array $codn_conce = null): Builder
    {
        $connection = $this->getConnectionName();

        // Subconsulta para obtener un único cargo por legajo
        $legajoCargo = DB::connection($connection)
            ->table('mapuche.dh21')
            ->select([
                DB::raw('DISTINCT ON (dh21.nro_legaj) dh21.nro_legaj'),
                'dh03.coddependesemp',
                'dh03.codc_uacad',
                'dh03.nro_cargo'
            ])
            ->join('mapuche.dh03', function($join) {
                $join->on('dh21.nro_legaj', '=', 'dh03.nro_legaj')
                    ->where('dh03.chkstopliq', '=', 0);
            })
            ->when(!is_null($codn_conce), function ($query) use ($codn_conce) {
                $query->where('dh21.codn_conce', $codn_conce);
            })
            ->where('dh21.nro_liqui', 2)
            ->groupBy(['dh21.nro_legaj', 'dh03.coddependesemp', 'dh03.codc_uacad', 'dh03.nro_cargo']);

        // Consulta principal
        return ConceptoListado::on($connection)
            ->from('mapuche.dh21')
            ->joinSub($legajoCargo, 'lc', function($join) {
                $join->on('dh21.nro_legaj', '=', 'lc.nro_legaj');
            })
            ->join('mapuche.dh01', 'dh21.nro_legaj', '=', 'dh01.nro_legaj')
            ->join('mapuche.dh22', 'dh21.nro_liqui', '=', 'dh22.nro_liqui')
            ->select([
                'dh21.nro_legaj',
                DB::raw("CONCAT(dh21.nro_legaj,'-',lc.coddependesemp,'-',dh21.nro_liqui,'-',dh21.codn_conce) as id"),
                'lc.codc_uacad',
                DB::raw("CONCAT(dh22.per_liano, LPAD(CAST(dh22.per_limes AS TEXT), 2, '0')) as periodo_fiscal"),
                'dh22.nro_liqui',
                'dh22.desc_liqui',
                'dh01.desc_appat',
                'dh01.desc_nombr',
                DB::raw("concat(dh01.nro_cuil1, dh01.nro_cuil, dh01.nro_cuil2) AS cuil"),
                'lc.coddependesemp',
                'lc.nro_cargo as secuencia',
                'dh21.codn_conce',
                'dh21.tipo_conce',
                'dh21.impp_conce'
            ])
            //->where('dh21.codn_conce', $codn_conce)
            ->where('dh21.nro_liqui', '=', 2)
            ->orderBy('lc.codc_uacad')
            ->orderBy('lc.coddependesemp');
    }

    public function getQueryForConceptoListado(int $codn_conce): Builder
    {
        return ConceptoListado::query();
    }

    public function getEmptyQuery(): Builder
    {
        // Creamos una subconsulta que mantenga la estructura de columnas
        $subquery = "
            SELECT
                NULL::varchar as codc_uacad,
                NULL::varchar as periodo_fiscal,
                NULL::integer as nro_liqui,
                NULL::varchar as desc_liqui,
                NULL::integer as nro_legaj,
                NULL::varchar as cuil,
                NULL::varchar as desc_appat,
                NULL::varchar as desc_nombr,
                NULL::varchar as coddependesemp,
                NULL::integer as secuencia,
                NULL::varchar as codn_conce,
                NULL::varchar as tipo_conce,
                NULL::decimal as impp_conce
            WHERE 1=0
        ";

        // Retornamos un Builder usando newFromBuilder
        return ConceptoListado::query()
        ->fromSub($subquery, 'conceptos_empty');
    }


    public function getQueryForConceptoRaw(int $codn_conce): Builder
    {
        return ConceptoListado::query()
            ->select([
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
                'categoria_completa AS cargo',
                'codn_conce',
                'tipo_conce',
                'impp_conce'
            ])
            ->where('codn_conce', $codn_conce)
            ->orderBy('codc_uacad')
            ->orderBy('oficina_pago');
    }
}
