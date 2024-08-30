<?php
namespace App\Repositories;

use App\Models\Dh11;
use App\Models\Dh61;

class Dh61Repository implements Dh61RepositoryInterface
{
    /**
     * Crea un nuevo registro histórico en la tabla Dh61 para la categoría especificada.
     *
     * @param Dh11 $category La categoría para la que se creará el registro histórico.
     * @return void
     */
    public function createHistoricalRecord(Dh11 $category): void
    {
        // Crear un nuevo registro histórico en Dh61
        // utilizando todos los campos de Dh11 $category
        // Dh61::create([
        //     'codc_categ' => $category->codc_categ,
        //     'impp_basic' => $category->impp_basic,
        //     'equivalencia' => $category->equivalencia,
        //     'tipo_escal' => $category->tipo_escal,
        //     'nro_escal' => $category->nro_escal,
        //     'codc_dedic' => $category->codc_dedic,
        //     'sino_mensu' => $category->sino_mensu,
        //     'sino_djpat' => $category->sino_djpat,
        //     'vig_caano' => $category->vig_caano,
        //     'vig_cames' => $category->vig_cames,
        //     'desc_categ' => $category->desc_categ,
        //     'sino_jefat' => $category->sino_jefat,
        //     'impp_asign' => $category->impp_asign,
        //     'computaantig' => $category->computaantig,
        //     'controlcargos' => $category->controlcargos,
        //     'controlhoras' => $category->controlhoras,
        //     'controlpuntos' => $category->controlpuntos,
        //     'controlpresup' => $category->controlpresup,
        //     'horasmenanual' => $category->horasmenanual,
        //     'cantpuntos' => $category->cantpuntos,
        //     'estadolaboral' => $category->estadolaboral,
        //     'nivel' => $category->nivel,
        //     'tipocargo' => $category->tipocargo,
        //     'remunbonif' => $category->remunbonif,
        //     'noremunbonif' => $category->noremunbonif,
        //     'remunnobonif' => $category->remunnobonif,
        //     'noremunnobonif' => $category->noremunnobonif,
        //     'otrasrem' => $category->otrasrem,
        //     'dto1610' => $category->dto1610,
        //     'reflaboral' => $category->reflaboral,
        //     'refadm95' => $category->refadm95,
        //     'critico' => $category->critico,
        //     'jefatura' => $category->jefatura,
        //     'gastosrepre' => $category->gastosrepre,
        //     'codigoescalafon' => $category->codigoescalafon,
        //     'noinformasipuver' => $category->noinformasipuver,
        //     'noinformasirhu' => $category->noinformasirhu,
        //     'imppnooblig' => $category->imppnooblig,
        //     'aportalao' => $category->aportalao,
        //     'factor_hs_catedra' => $category->factor_hs_catedra,
        // ]);

        $dh61 = new Dh61();
        $dh61->fill($category->toArray());
        $dh61->save();
    }
}
