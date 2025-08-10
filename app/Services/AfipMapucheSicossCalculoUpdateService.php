<?php

declare(strict_types=1);

namespace App\Services;

use App\Traits\DynamicConnectionTrait;
use Illuminate\Support\Facades\DB;

class AfipMapucheSicossCalculoUpdateService
{
    use DynamicConnectionTrait;

    public function updateUacadAndCaracter(string $table = 'afip_mapuche_sicoss_calculos'): array
    {
        try {
            $affected = DB::connection($this->getConnectionName())->update("
                UPDATE suc.afip_mapuche_sicoss_calculos a
                SET codc_uacad = subq.codc_uacad,
                    caracter   = subq.caracter
                FROM (WITH cuils AS (SELECT dh01.nro_legaj,
                                            CONCAT( dh01.nro_cuil1::TEXT, LPAD( dh01.nro_cuil::CHARACTER(8)::TEXT, 8, '0'::TEXT ),
                                                    dh01.nro_cuil2::TEXT ) AS cuil
                                     FROM mapuche.dh01
                	                          JOIN suc.afip_mapuche_sicoss_calculos am ON CONCAT( dh01.nro_cuil1::TEXT,
                	                                                                              LPAD( dh01.nro_cuil::CHARACTER(8)::TEXT, 8, '0'::TEXT ),
                	                                                                              dh01.nro_cuil2::TEXT ) = am.cuil),
                           dh03_agregado AS (SELECT nro_legaj, MAX( codc_uacad ) AS codc_uacad, MIN( codc_carac ) AS codc_carac
                                             FROM mapuche.dh03
                                             GROUP BY nro_legaj)
                      SELECT c.cuil, d.codc_uacad, CASE WHEN d.codc_carac IN ( 'PERM', 'REGU' ) THEN 'PERM' ELSE 'CONT' END AS caracter
                      FROM cuils c
                	           JOIN dh03_agregado d ON c.nro_legaj = d.nro_legaj) subq
                WHERE a.cuil = subq.cuil
            ");

            return [
                'success' => true,
                'affected' => $affected,
                'message' => "Se actualizaron {$affected} registros correctamente",
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'affected' => 0,
                'message' => "Error en la actualización: {$e->getMessage()}",
            ];
        }
    }
}
