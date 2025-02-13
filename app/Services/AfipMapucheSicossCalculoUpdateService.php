<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\AfipMapucheSicoss;
use Illuminate\Support\Facades\DB;
use App\Traits\MapucheConnectionTrait;
use App\Models\AfipMapucheSicossCalculo;

class AfipMapucheSicossCalculoUpdateService
{
    use MapucheConnectionTrait;
    public function updateFromSicoss(string $periodoFiscal): array
    {
        $updated = 0;
        $errors = [];

        $registros = AfipMapucheSicossCalculo::all();

        foreach ($registros as $registro) {
            try {
                DB::connection($this->getConnectionName())->beginTransaction();

                $sicossData = AfipMapucheSicoss::where('periodo_fiscal', $periodoFiscal)
                    ->where('cuil', $registro->cuil)
                    ->first();

                if ($sicossData) {
                    $registro->update([
                        'remtotal' => $sicossData->rem_total,
                        'rem1' => $sicossData->rem_impo1,
                        'rem2' => $sicossData->rem_impo2
                    ]);
                    $updated++;
                }

                DB::connection($this->getConnectionName())->commit();
            } catch (\Exception $e) {
                DB::connection($this->getConnectionName())->rollBack();
                $errors[] = "Error actualizando CUIL {$registro->cuil}: {$e->getMessage()}";
            }
        }

        return [
            'updated' => $updated,
            'errors' => $errors
        ];
    }

    public function updateUacadAndCaracter(): array
    {
        try {
            $affected = DB::connection($this->getConnectionName())->update("
                WITH cuils AS (
                    SELECT dh01.nro_legaj,
                           CONCAT(dh01.nro_cuil1::text,
                                LPAD(dh01.nro_cuil::CHARACTER(8)::TEXT, 8, '0'::TEXT),
                                dh01.nro_cuil2::text) AS cuil
                    FROM mapuche.dh01
                    JOIN suc.afip_mapuche_sicoss_calculos am ON
                        CONCAT(dh01.nro_cuil1::text,
                               LPAD(dh01.nro_cuil::CHARACTER(8)::TEXT, 8, '0'::TEXT),
                               dh01.nro_cuil2::text) = am.cuil
                ),
                dh03_agregado AS (
                    SELECT nro_legaj,
                           MAX(codc_uacad) as codc_uacad,
                           MIN(codc_carac) as codc_carac
                    FROM mapuche.dh03
                    GROUP BY nro_legaj
                )
                UPDATE suc.afip_mapuche_sicoss_calculos a
                SET codc_uacad = subq.codc_uacad,
                    caracter = CASE
                                WHEN subq.codc_carac IN ('PERM', 'REGU')
                                THEN 'PERM'
                                ELSE 'CONT'
                              END
                FROM (
                    SELECT c.cuil, d.codc_uacad, d.codc_carac
                    FROM cuils c
                    JOIN dh03_agregado d ON c.nro_legaj = d.nro_legaj
                ) subq
                WHERE a.cuil = subq.cuil
            ");

            return [
                'success' => true,
                'affected' => $affected,
                'message' => "Se actualizaron {$affected} registros correctamente"
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'affected' => 0,
                'message' => "Error en la actualización: {$e->getMessage()}"
            ];
        }
    }
}
