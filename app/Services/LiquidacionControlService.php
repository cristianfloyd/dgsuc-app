<?php

namespace App\Services;

use App\Data\ControlResultData;
use Illuminate\Support\Facades\DB;

class LiquidacionControlService
{
    public function controlarCargosLiquidados(int $nroLiqui): ControlResultData
    {
        $result = DB::select("
            select a.nro_legaj, a.nro_cargo, c.codigoescalafon, a.codc_agrup,
                   a.codc_categ, a.codc_carac, c.desc_categ
            from mapuche.dh03 a, mapuche.dh21 b, mapuche.dh11 c
            where a.nro_legaj = b.nro_legaj
            and a.nro_cargo = b.nro_cargo
            and a.codc_categ = c.codc_categ
            and nro_liqui = ?
            group by a.nro_legaj, a.nro_cargo, c.codigoescalafon,
                     a.codc_agrup, a.codc_categ, a.codc_carac, c.desc_categ
        ", [$nroLiqui]);

        return new ControlResultData(
            success: count($result) > 0,
            message: 'Se encontraron ' . count($result) . ' cargos liquidados',
            data: $result
        );
    }

    public function controlarNegativos(int $nroLiqui): ControlResultData
    {
        $result = DB::select("
            select nro_legaj, nro_cargo,
            sum(case when codn_conce >= 100 and codn_conce < 200 then impp_conce
                     when codn_conce >= 200 and codn_conce < 300 then impp_conce * -1
                end)::numeric::money as Neto
            from mapuche.dh21
            where nro_liqui = ?
            group by nro_legaj, nro_cargo
            having sum(case when codn_conce >= 100 and codn_conce < 200 then impp_conce
                           when codn_conce >= 200 and codn_conce < 300 then impp_conce * -1
                      end)::numeric::money < 0::money
        ", [$nroLiqui]);

        return new ControlResultData(
            success: count($result) === 0,
            message: count($result) > 0 ? 'Se encontraron ' . count($result) . ' cargos con neto negativo' : 'No se encontraron netos negativos',
            data: $result
        );
    }
}
