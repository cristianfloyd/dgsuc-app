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
}
