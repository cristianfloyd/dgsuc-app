<?php

namespace App\Services;

use App\Repositories\Dh11RepositoryInterface;
use App\Repositories\Dh61Repository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Dh11RestoreService
{
    /**
     * Constructor del servicio.
     *
     * @param Dh61Repository $dh61Repository
     * @param Dh11RepositoryInterface $dh11Repository
     */
    public function __construct(
        protected Dh61Repository          $dh61Repository,
        protected Dh11RepositoryInterface $dh11Repository,
    ) {
    }

    /**
     * Restaura las categorías para un período fiscal específico.
     *
     * @param int $year
     * @param int $month
     *
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

            $updatedRecords = $this->restoreHistoricalRecords($historicalRecords);
            Log::debug('Registros actualizados: ' . array_count_values($updatedRecords));


            DB::commit();
            Log::info("Restauración completada con éxito para el período {$year}-{$month}");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error durante la restauración: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Restaura los registros históricos de categorías en la tabla dh11.
     *
     * @param \Illuminate\Support\Collection $historicalRecords Colección de registros históricos a restaurar.
     *
     * @throws \Exception Si ocurre un error durante la actualización de los registros.
     *
     * @return array Arreglo de registros actualizados.
     */
    private function restoreHistoricalRecords($historicalRecords): array
    {
        DB::beginTransaction();

        try {
            $updatedRecords = [];

            foreach ($historicalRecords as $record) {
                $this->dh11Repository->update(
                    [
                        'codc_categ' => $record->codc_categ,
                        'vig_caano' => $record->vig_caano,
                        'vig_cames' => $record->vig_cames,
                    ],
                    $record,
                );

                $updatedRecords[] = $record;
            }

            DB::commit();

            return $updatedRecords;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error actualizando los registros: ' . $e->getMessage());
            throw $e; // Re-lanzar la excepción para que el llamador pueda manejarla
        }
    }
}
