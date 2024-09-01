<?php

namespace App\Services;

use App\Repositories\Dh61Repository;
use App\Repositories\Dh11Repository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Dh11RestoreService
{
    protected $dh61Repository;
    protected $dh11Repository;

    /**
     * Constructor del servicio.
     *
     * @param Dh61Repository $dh61Repository
     * @param Dh11Repository $dh11Repository
     */
    public function __construct(Dh61Repository $dh61Repository, Dh11Repository $dh11Repository)
    {
        $this->dh61Repository = $dh61Repository;
        $this->dh11Repository = $dh11Repository;
    }


    /**
     * Restaura las categorías para un período fiscal específico.
     *
     * @param int $year
     * @param int $month
     * @throws \Exception
     */
    public function restoreFiscalPeriod(int $year, int $month): void
    {
        Log::info("Iniciando restauración de categorías para el período {$year}-{$month}");

        DB::beginTransaction();

        try {
             // Obtener registros actuales de dh11
            $currentRecords = $this->dh11Repository->getAllCurrentRecords();
            // Guardar registros actuales en dh61 si no existen
            foreach ($currentRecords as $record) {
                if (!$this->dh61Repository->exists($record->codc_categ, $record->vig_caano, $record->vig_cames)) {
                    $this->dh61Repository->create($record->toArray());
                    Log::info("Registro actual guardado en histórico: {$record->codc_categ} para {$record->vig_caano}-{$record->vig_cames}");
                }
            }

            // Obtener registros históricos del período especificado
            $historicalRecords = $this->dh61Repository->getRecordsByFiscalPeriod($year, $month);

            if ($historicalRecords->isEmpty()) {
                throw new \Exception("No se encontraron registros históricos para el período {$year}-{$month}");
            }

            foreach ($historicalRecords as $record) {
                // Actualizar o crear el registro en dh11
                $this->dh11Repository->updateOrCreate(
                    [
                        'codc_categ' => $record->codc_categ,
                        'vig_caano' => $record->vig_caano,
                        'vig_cames' => $record->vig_cames
                    ],
                    $record->toArray()
                );

                Log::info("Categoría restaurada: {$record->codc_categ} para {$year}-{$month}");
            }

            DB::commit();
            Log::info("Restauración completada con éxito para el período {$year}-{$month}");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error durante la restauración: " . $e->getMessage());
            throw $e;
        }
    }
}

